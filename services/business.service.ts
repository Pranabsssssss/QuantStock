import { apiClient } from "@/api/client";
import type {
  InterviewMessageRequest,
  InterviewMessageResponse,
  InterviewStartResponse,
} from "@/types/api";

export const businessService = {
  startInterview() {
    return apiClient.post<InterviewStartResponse>("/business/interview/start").then((res) => res.data);
  },
  sendInterviewMessage(payload: InterviewMessageRequest) {
    return apiClient.post<InterviewMessageResponse>("/business/interview/message", payload).then((res) => res.data);
  },
  confirmInterview(payload: { session_id: string }) {
    return apiClient.post("/business/interview/confirm", payload).then((res) => res.data);
  },
  getProfile() {
    return apiClient.get<Record<string, unknown>>("/business/profile").then((res) => res.data);
  },
};
