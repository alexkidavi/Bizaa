/**
 * Static UI config for marketplace category display.
 * Keys are lowercase category identifiers that match ecommerce_stores.category
 * and are used for fuzzy-matching product category names.
 */
export const categoryConfig = [
  { id: "electronics",  label: "Electronics",    color: "#3b82f6", bg: "#eff6ff",
    icon: `<path d="M9 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-4M9 3a2 2 0 0 0 4 0M9 3a2 2 0 0 1 4 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>` },
  { id: "fashion",      label: "Fashion",         color: "#ec4899", bg: "#fdf2f8",
    icon: `<path d="M20.38 3.46L16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.57a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.57a2 2 0 0 0-1.34-2.23z" stroke="currentColor" stroke-width="1.8" fill="none"/>` },
  { id: "groceries",    label: "Groceries",       color: "#22c55e", bg: "#f0fdf4",
    icon: `<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" stroke="currentColor" stroke-width="1.8" fill="none"/><line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="1.8"/>` },
  { id: "hardware",     label: "Hardware",        color: "#f97316", bg: "#fff7ed",
    icon: `<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" stroke="currentColor" stroke-width="1.8" fill="none"/>` },
  { id: "beauty",       label: "Beauty",          color: "#a855f7", bg: "#faf5ff",
    icon: `<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke="currentColor" stroke-width="1.8" fill="none"/>` },
  { id: "food",         label: "Food & Drinks",   color: "#ef4444", bg: "#fef2f2",
    icon: `<path d="M18 8h1a4 4 0 0 1 0 8h-1M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8zM6 1v3M10 1v3M14 1v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" fill="none"/>` },
  { id: "home",         label: "Home & Living",   color: "#14b8a6", bg: "#f0fdfa",
    icon: `<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="1.8" fill="none"/><polyline points="9 22 9 12 15 12 15 22" stroke="currentColor" stroke-width="1.8"/>` },
  { id: "sports",       label: "Sports",          color: "#eab308", bg: "#fefce8",
    icon: `<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8" fill="none"/><path d="M12 8v4l3 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>` },
  { id: "auto",         label: "Auto Parts",      color: "#64748b", bg: "#f8fafc",
    icon: `<rect x="1" y="3" width="15" height="13" rx="2" stroke="currentColor" stroke-width="1.8" fill="none"/><path d="M16 8h4l3 3v5h-7V8zM5.5 17a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM18.5 17a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z" stroke="currentColor" stroke-width="1.8" fill="none"/>` },
  { id: "books",        label: "Books",           color: "#8b5cf6", bg: "#f5f3ff",
    icon: `<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" fill="none"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke="currentColor" stroke-width="1.8" fill="none"/>` },
  { id: "other",        label: "Other",           color: "#94a3b8", bg: "#f8fafc",
    icon: `<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8" fill="none"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>` },
];

/** Find config by store category id or product category name (fuzzy). */
export function getCategoryConfig(key) {
  if (!key) return categoryConfig[categoryConfig.length - 1]; // other
  const k = key.toLowerCase();
  return (
    categoryConfig.find(c => c.id === k) ??
    categoryConfig.find(c => k.includes(c.id) || c.id.includes(k)) ??
    categoryConfig[categoryConfig.length - 1]
  );
}

export function getCategoryIcon(key) {
  return getCategoryConfig(key)?.icon ?? "";
}

export function getCategoryColor(key) {
  return getCategoryConfig(key)?.color ?? "#229de7";
}
