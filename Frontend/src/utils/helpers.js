// src/utils/helpers.js

export function truncateWords(text, maxLength = 40) {
  if (!text) return "";
  return text.length > maxLength ? text.substring(0, maxLength) + "..." : text;
}

export function toYMD(date) {
  return date.toISOString().split("T")[0];
}
