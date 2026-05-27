/**
 * ContextMenu.jsx — Right-click context menu
 * FIXED: Better positioning, keyboard navigation, separator support
 */

import React, { useEffect, useRef, memo } from 'react';
import './ContextMenu.css';

function ContextMenu({ x, y, items, onClose }) {
  const menuRef = useRef(null);

  useEffect(() => {
    const handleClickOutside = (e) => {
      if (menuRef.current && !menuRef.current.contains(e.target)) {
        onClose();
      }
    };

    const handleEscape = (e) => {
      if (e.key === 'Escape') onClose();
    };

    // Delay adding listeners to prevent immediate close
    const timer = setTimeout(() => {
      document.addEventListener('mousedown', handleClickOutside);
      document.addEventListener('contextmenu', handleClickOutside);
      document.addEventListener('keydown', handleEscape);
    }, 10);

    return () => {
      clearTimeout(timer);
      document.removeEventListener('mousedown', handleClickOutside);
      document.removeEventListener('contextmenu', handleClickOutside);
      document.removeEventListener('keydown', handleEscape);
    };
  }, [onClose]);

  // Calculate adjusted position to keep menu in viewport (clamp to >= 0)
  const itemCount = items.filter((it) => !it.separator).length;
  const separatorCount = items.length - itemCount;
  const estimatedHeight = itemCount * 38 + separatorCount * 9 + 16;
  const adjustedX = Math.max(0, Math.min(x, window.innerWidth - 200));
  const adjustedY = Math.max(0, Math.min(y, window.innerHeight - estimatedHeight));

  return (
    <div
      ref={menuRef}
      className="context-menu"
      role="menu"
      aria-orientation="vertical"
      style={{ top: `${adjustedY}px`, left: `${adjustedX}px` }}
    >
      {items.map((item, i) => {
        if (item.separator) {
          return <div key={`sep-${i}`} className="context-menu__separator" />;
        }

        return (
          <button
            key={i}
            role="menuitem"
            onClick={() => {
              if (!item.disabled) {
                item.onClick();
                onClose();
              }
            }}
            disabled={item.disabled}
            className={`context-menu__item ${item.disabled ? 'context-menu__item--disabled' : ''}`}
          >
            {item.icon && <i className={`${item.icon} context-menu__icon`} />}
            <span className="context-menu__label">{item.label}</span>
            {item.shortcut && (
              <span className="context-menu__shortcut">{item.shortcut}</span>
            )}
          </button>
        );
      })}
    </div>
  );
}

export default memo(ContextMenu);
