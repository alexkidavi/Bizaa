<template>
  <div class="guidance-box">
    <div class="guidance-icon">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 17h.007v.008H12V17z" />
      </svg>
    </div>
    <div class="guidance-body">
      <div class="guidance-title">
        <slot name="title">Where to get this</slot>
      </div>
      <div class="guidance-content">
        <slot />
      </div>
      <a v-if="href" :href="href" target="_blank" rel="noopener" class="guidance-link">
        {{ linkLabel }}
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V13.5m-9 3L20.25 3m0 0h-5.5m5.5 0v5.5" />
        </svg>
      </a>
    </div>
  </div>
</template>

<script setup>
defineProps({
  href: { type: String, default: "" },
  linkLabel: { type: String, default: "Open" },
});
</script>

<style scoped>
.guidance-box {
  display: flex;
  gap: 10px;
  padding: 12px 14px;
  background: #f5faff;
  border: 1px solid #d9ebf7;
  border-radius: 12px;
  margin-bottom: 18px;
}
.guidance-icon {
  flex-shrink: 0;
  width: 26px;
  height: 26px;
  border-radius: 8px;
  background: #eaf5fd;
  border: 1px solid #b3defa;
  color: #229de7;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 1px;
}
.guidance-body {
  flex: 1;
  min-width: 0;
}
.guidance-title {
  font-size: 11px;
  font-weight: 800;
  color: #1682c4;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 5px;
}
.guidance-content {
  font-size: 12px;
  line-height: 1.65;
  color: #475569;
}
.guidance-content :deep(ol),
.guidance-content :deep(ul) {
  margin: 4px 0 0;
  padding-left: 18px;
  display: flex;
  flex-direction: column;
  gap: 3px;
}
.guidance-content :deep(strong) {
  color: #1e293b;
  font-weight: 700;
}
.guidance-content :deep(code) {
  background: #eaf5fd;
  border: 1px solid #d9ebf7;
  border-radius: 4px;
  padding: 1px 5px;
  font-size: 11px;
  font-family: monospace;
  color: #1682c4;
}
.guidance-link {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  margin-top: 8px;
  font-size: 11px;
  font-weight: 700;
  color: #229de7;
  text-decoration: none;
}
.guidance-link:hover {
  color: #1682c4;
  text-decoration: underline;
}
</style>
