import { apiClient } from "@/api/client";

export const settingsService = {
  getSettings() {
    return apiClient.get("/settings").then((res) => res.data);
  },
  updateSettings(payload: Record<string, unknown>) {
    return apiClient.post("/settings", payload).then((res) => res.data);
  },
};
