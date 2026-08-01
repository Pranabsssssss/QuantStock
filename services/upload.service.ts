import { apiClient } from "@/api/client";
import type { UploadSummary } from "@/types/api";

export const uploadService = {
  uploadCsv(formData: FormData, onUploadProgress?: (progress: number) => void) {
    return apiClient
      .post<UploadSummary>("/upload/csv", formData, {
        headers: { "Content-Type": "multipart/form-data" },
        onUploadProgress: (event) => {
          if (!event.total || !onUploadProgress) return;
          onUploadProgress(Math.round((event.loaded * 100) / event.total));
        },
      })
      .then((res) => res.data);
  },
};
