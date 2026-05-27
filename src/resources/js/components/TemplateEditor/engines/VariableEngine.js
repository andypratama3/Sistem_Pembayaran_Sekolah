/**
 * VariableEngine.js — Frontend variable resolution engine
 *
 * Resolves {{var}} placeholders against a runtime data context.
 *
 * Supported syntax:
 *   {{student.name}}              — simple/nested path
 *   {{invoice.total}}             — nested object
 *   {{#items}}...{{/items}}       — repeatable section (returns array of resolved strings)
 *   {{#if cond}}...{{/if}}        — conditional section
 *   {{computed.average}}          — computed alias (resolved like a path)
 *
 * The engine is purely functional. It performs NO HTML escaping — caller is
 * responsible for escaping when rendering to the DOM. For Konva Text nodes,
 * the resolved string is set via `node.text(...)` which is text-only.
 */

const VAR_REGEX = /\{\{\s*([^#/}][^}]*?)\s*\}\}/g;
// Section blocks. We use a unified regex matching:
//   {{#name}}...{{/name}}              — backreference closes with same name
//   {{#if path}}...{{/path}}           — closing tag uses the path name
//   {{#if path}}...{{/if}}             — alternative form using literal "if"
// Order matters: the explicit "/if" alternation must come first so
// `{{#if flag}}NEVER{{/flag}}` doesn't accidentally consume an unrelated
// outer `{{/flag}}`. The named-section form falls back to backreference match.
const IF_SECTION_REGEX = /\{\{#if\s+([\w.]+)\}\}([\s\S]*?)\{\{\/(?:if|\1)\}\}/g;
const NAMED_SECTION_REGEX = /\{\{#([\w.]+)\}\}([\s\S]*?)\{\{\/\1\}\}/g;

const MAX_RECURSION_DEPTH = 32;
const MAX_TEMPLATE_LENGTH = 1_000_000;

export class VariableEngine {
  /**
   * @param {object} context  — { student: {...}, grades: [...], system: {...} }
   * @param {object} [options]
   * @param {string} [options.fallback='–']  — value when variable is undefined
   * @param {boolean} [options.strict=false] — throw on missing variable instead of fallback
   */
  constructor(context = {}, options = {}) {
    this.context = context;
    this.fallback = options.fallback ?? '–';
    this.strict = options.strict === true;
    this._depth = options._depth ?? 0;
  }

  /**
   * Resolve all placeholders in a template string.
   *
   * @param {string} template
   * @returns {string}
   */
  resolve(template) {
    if (typeof template !== 'string' || template.length === 0) {
      return template ?? '';
    }

    // Prevent processing extremely large templates
    if (template.length > MAX_TEMPLATE_LENGTH) {
      console.warn('[VariableEngine] Template exceeds 1MB, truncating');
      template = template.substring(0, MAX_TEMPLATE_LENGTH);
    }

    // Guard against runaway recursion (e.g., section bodies that re-introduce themselves).
    if (this._depth > MAX_RECURSION_DEPTH) {
      console.warn('[VariableEngine] Maximum recursion depth exceeded');
      return '';
    }

    // 1a. Resolve {{#if path}}...{{/if}} sections.
    let output = template.replace(IF_SECTION_REGEX, (_, path, body) => {
      const value = this.getNestedValue(path);
      if (!value) return '';
      const child = this._childEngine(this.context);
      return child.resolve(body);
    });

    // 1b. Resolve {{#name}}...{{/name}} sections (loops + scoped objects).
    output = output.replace(NAMED_SECTION_REGEX, (_, path, body) => {
      const value = this.getNestedValue(path);

      if (Array.isArray(value)) {
        return value.map((item) => {
          const itemContext = (item && typeof item === 'object')
            ? { ...this.context, ...item, this: item, item }
            : { ...this.context, this: item, item };
          const childEngine = this._childEngine(itemContext);
          const result = childEngine.resolve(body);
          // Explicit cleanup for garbage collection
          childEngine.context = null;
          return result;
        }).join('');
      }

      if (value && typeof value === 'object') {
        const childEngine = this._childEngine({ ...this.context, ...value });
        const result = childEngine.resolve(body);
        childEngine.context = null;
        return result;
      }

      return '';
    });

    // 2. Resolve simple variables.
    output = output.replace(VAR_REGEX, (match, rawPath) => {
      const path = rawPath.trim();
      const value = this.getNestedValue(path);

      if (value === undefined || value === null) {
        if (this.strict) {
          throw new Error(`Unresolved variable: {{${path}}}`);
        }
        return this.fallback;
      }

      return String(value);
    });

    return output;
  }

  _childEngine(context) {
    return new VariableEngine(context, {
      fallback: this.fallback,
      strict: this.strict,
      _depth: this._depth + 1,
    });
  }

  /**
   * Resolve a nested path against the context (e.g., "student.address.city").
   * Returns undefined if any segment is missing.
   *
   * @param {string} path
   * @returns {*}
   */
  getNestedValue(path) {
    if (!path) return undefined;

    return path.split('.').reduce((curr, key) => {
      if (curr === null || curr === undefined) return undefined;
      // Prevent prototype pollution
      if (key === '__proto__' || key === 'constructor' || key === 'prototype') {
        return undefined;
      }
      // Support array index: items.0.name
      if (Array.isArray(curr) && /^\d+$/.test(key)) {
        const idx = Number(key);
        // Bounds check to prevent reading past array end (returns undefined naturally,
        // but explicit for clarity).
        if (idx < 0 || idx >= curr.length) return undefined;
        return curr[idx];
      }
      // Only traverse own enumerable properties to avoid inherited prototype access.
      if (typeof curr !== 'object' || !Object.prototype.hasOwnProperty.call(curr, key)) {
        return undefined;
      }
      return curr[key];
    }, this.context);
  }

  /**
   * Extract all unique placeholder paths from a template string.
   *
   * @param {string} template
   * @returns {string[]}
   */
  static extract(template) {
    if (typeof template !== 'string') return [];

    const paths = new Set();
    let m;

    // {{#if path}} sections
    const ifRe = new RegExp(IF_SECTION_REGEX.source, 'g');
    while ((m = ifRe.exec(template)) !== null) {
      paths.add(m[1]);
    }

    // {{#name}} sections
    const namedRe = new RegExp(NAMED_SECTION_REGEX.source, 'g');
    while ((m = namedRe.exec(template)) !== null) {
      paths.add(m[1]);
    }

    // Variable paths.
    const varRe = new RegExp(VAR_REGEX.source, 'g');
    while ((m = varRe.exec(template)) !== null) {
      paths.add(m[1].trim());
    }

    return Array.from(paths);
  }

  /**
   * Quick one-shot helper.
   *
   * @param {string} template
   * @param {object} context
   * @returns {string}
   */
  static interpolate(template, context) {
    return new VariableEngine(context).resolve(template);
  }
}

export default VariableEngine;
