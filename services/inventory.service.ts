import { apiClient } from "@/api/client";
import type { PaginatedInventory } from "@/types/api";

export const inventoryService = {
  getInventory(params?: Record<string, string | number>) {
    return apiClient.get<PaginatedInventory | { items?: unknown[] }>("/inventory", { params }).then((res) => res.data);
  },
};
