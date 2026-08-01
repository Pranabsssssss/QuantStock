import { apiClient } from "@/api/client";

export const forecastService = {
  getForecast() {
    return apiClient.get("/forecast").then((res) => res.data);
  },
};
