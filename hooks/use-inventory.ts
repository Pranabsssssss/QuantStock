"use client";

import { useQuery } from "@tanstack/react-query";
import { inventoryService } from "@/services/inventory.service";

export const useInventory = (params?: Record<string, string | number>) =>
  useQuery({
    queryKey: ["inventory", params],
    queryFn: () => inventoryService.getInventory(params),
  });
