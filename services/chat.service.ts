import { apiClient } from "@/api/client";
import type { ChatResponse } from "@/types/api";

export const chatService = {
  sendMessage(payload: { message: string }) {
    return apiClient.post<ChatResponse>("/chat", payload).then((res) => res.data);
  },
};
