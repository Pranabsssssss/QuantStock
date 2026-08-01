import { apiClient } from "@/api/client";
import type { VoiceResponse } from "@/types/api";

export const voiceService = {
  uploadVoice(formData: FormData) {
    return apiClient
      .post<VoiceResponse>("/voice", formData, { headers: { "Content-Type": "multipart/form-data" } })
      .then((res) => res.data);
  },
};
