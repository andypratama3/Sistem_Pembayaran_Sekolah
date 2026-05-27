import { describe, it, expect } from 'vitest';
import { VariableEngine } from '../VariableEngine';

describe('VariableEngine', () => {
  describe('simple variables', () => {
    it('replaces {{var}} placeholders', () => {
      const engine = new VariableEngine({ name: 'Budi' });
      expect(engine.resolve('Hello {{name}}')).toBe('Hello Budi');
    });

    it('falls back to default character when missing', () => {
      const engine = new VariableEngine({});
      expect(engine.resolve('Hello {{name}}')).toBe('Hello –');
    });

    it('respects custom fallback', () => {
      const engine = new VariableEngine({}, { fallback: 'N/A' });
      expect(engine.resolve('{{x}}')).toBe('N/A');
    });

    it('throws on missing variable in strict mode', () => {
      const engine = new VariableEngine({}, { strict: true });
      expect(() => engine.resolve('{{x}}')).toThrow(/Unresolved variable/);
    });
  });

  describe('nested paths', () => {
    it('resolves dot-notation paths', () => {
      const engine = new VariableEngine({ student: { address: { city: 'Samarinda' } } });
      expect(engine.resolve('{{student.address.city}}')).toBe('Samarinda');
    });

    it('returns fallback when an intermediate segment is missing', () => {
      const engine = new VariableEngine({ student: null });
      expect(engine.resolve('{{student.name}}')).toBe('–');
    });

    it('supports numeric array indexing', () => {
      const engine = new VariableEngine({ items: [{ name: 'A' }, { name: 'B' }] });
      expect(engine.resolve('{{items.1.name}}')).toBe('B');
    });
  });

  describe('section blocks', () => {
    it('renders {{#if}} body when truthy', () => {
      const engine = new VariableEngine({ flag: true, name: 'Budi' });
      const out = engine.resolve('{{#if flag}}Hi {{name}}{{/flag}}');
      expect(out).toBe('Hi Budi');
    });

    it('omits {{#if}} body when falsy', () => {
      const engine = new VariableEngine({ flag: false });
      expect(engine.resolve('{{#if flag}}NEVER{{/flag}}')).toBe('');
    });

    it('repeats body for each item in an array section', () => {
      const engine = new VariableEngine({
        items: [{ name: 'Alice' }, { name: 'Bob' }],
      });
      const out = engine.resolve('{{#items}}<p>{{name}}</p>{{/items}}');
      expect(out).toBe('<p>Alice</p><p>Bob</p>');
    });

    it('renders an object section by spreading into scope', () => {
      const engine = new VariableEngine({ student: { name: 'Budi', nis: '123' } });
      const out = engine.resolve('{{#student}}{{name}}-{{nis}}{{/student}}');
      expect(out).toBe('Budi-123');
    });
  });

  describe('static helpers', () => {
    it('extract() returns unique placeholder paths', () => {
      const paths = VariableEngine.extract('{{a}} {{b}} {{a}} {{#x}}...{{/x}}');
      expect(paths).toContain('a');
      expect(paths).toContain('b');
      expect(paths).toContain('x');
      expect(paths.length).toBe(3);
    });

    it('interpolate() one-shot resolves a string', () => {
      expect(VariableEngine.interpolate('Hi {{n}}', { n: 'Z' })).toBe('Hi Z');
    });

    it('extract() returns empty array for non-string input', () => {
      expect(VariableEngine.extract(null)).toEqual([]);
      expect(VariableEngine.extract(123)).toEqual([]);
    });
  });
});
