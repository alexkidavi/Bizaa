<template>
  <div>
    <label class="field-label">{{ label }}</label>
    <div class="relative">
      <input
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :type="showInput ? 'text' : 'password'"
        class="field-input pr-14"
        :placeholder="placeholder"
      />
      <button type="button" @click="showInput = !showInput" class="mask-toggle">
        {{ showInput ? "Hide" : "Show" }}
      </button>
    </div>

    <div v-if="isSet" class="saved-row">
      <button
        type="button"
        @click="reveal"
        :disabled="revealing"
        class="reveal-btn"
      >
        {{ revealing ? "Loading…" : revealedMask ? "Refresh saved value" : "Show saved value" }}
      </button>
      <span v-if="revealedMask" class="revealed-mask">{{ revealedMask }}</span>
    </div>
    <p v-else class="field-hint">{{ hint }}</p>
  </div>
</template>

<script setup>
import { ref } from "vue";
import BusinessService from "@/services/BusinessService";

const props = defineProps({
  modelValue: { type: String, default: "" },
  label: { type: String, required: true },
  placeholder: { type: String, default: "" },
  fieldKey: { type: String, required: true },
  isSet: { type: Boolean, default: false },
  hint: { type: String, default: "" },
});

defineEmits(["update:modelValue"]);

const showInput = ref(false);
const revealing = ref(false);
const revealedMask = ref("");

const reveal = async () => {
  revealing.value = true;
  try {
    const res = await BusinessService.revealSecret(props.fieldKey);
    revealedMask.value = res?.data?.masked || "(none)";
  } catch {
    revealedMask.value = "Could not load.";
  } finally {
    revealing.value = false;
  }
};
</script>

<style scoped>
.field-label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 6px;
}
.field-input {
  width: 100%;
  padding: 10px 14px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 12px;
  font-size: 13.5px;
  color: #1e293b;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
  font-family: inherit;
}
.field-input:focus {
  border-color: #229de7;
  box-shadow: 0 0 0 3px rgba(34, 157, 231, 0.12);
  background: #fff;
}
.field-hint {
  font-size: 10.5px;
  color: #94a3b8;
  margin-top: 6px;
}
.mask-toggle {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 11px;
  font-weight: 700;
  color: #94a3b8;
}
.mask-toggle:hover {
  color: #229de7;
}
.saved-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 6px;
}
.reveal-btn {
  font-size: 10.5px;
  font-weight: 700;
  color: #229de7;
  background: #eaf5fd;
  border: 1px solid #b3defa;
  border-radius: 6px;
  padding: 3px 8px;
  cursor: pointer;
  transition: all 0.15s;
}
.reveal-btn:hover:not(:disabled) {
  background: #229de7;
  color: white;
}
.reveal-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.revealed-mask {
  font-family: monospace;
  font-size: 11px;
  color: #475569;
  background: #f5faff;
  border: 1px solid #d9ebf7;
  border-radius: 6px;
  padding: 2px 8px;
}
</style>
