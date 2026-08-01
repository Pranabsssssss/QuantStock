import { apiClient } from "@/api/client";

export const notificationsService = {
  list() {
    return apiClient.get<Array<Record<string, unknown>>>("/notifications").then((res) => res.data);
  },
};
