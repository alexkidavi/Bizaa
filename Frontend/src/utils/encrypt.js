import CryptoJS from "crypto-js";

const SECRET_KEY = import.meta.env.VITE_ENCRYPTION_KEY || "crm-secret-key";

export function encryptId(id) {
  return CryptoJS.AES.encrypt(id.toString(), SECRET_KEY).toString();
}

export function decryptId(encryptedId) {
  try {
    const bytes = CryptoJS.AES.decrypt(encryptedId, SECRET_KEY);
    return bytes.toString(CryptoJS.enc.Utf8);
  } catch {
    return null;
  }
}
