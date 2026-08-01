"use client";

import { useState } from "react";
import { InventoryTable } from "@/components/inventory/inventory-table";
import { EmptyState } from "@/components/ui/empty-state";
import { ErrorState } from "@/components/ui/error-state";
import { GlassCard } from "@/components/ui/glass-card";
import { LoadingSkeleton } from "@/components/ui/loading-skeleton";
import { PageHeading } from "@/components/ui/page-heading";
import { getErrorMessage } from "@/lib/error-message";
import { useInventory } from "@/hooks/use-inventory";
import type { InventoryItem } from "@/types/api";

export default function InventoryPage() {
  const [query, setQuery] = useState("");
  const inventoryQuery = useInventory(query ? { search: query } : undefined);

  if (inventoryQuery.isLoading) return <LoadingSkeleton className="h-80" />;
  if (inventoryQuery.isError) return <ErrorState message={getErrorMessage(inventoryQuery.error)} onRetry={() => inventoryQuery.refetch()} />;

  const rawData = inventoryQuery.data;
  const items = Array.isArray((rawData as { items?: unknown[] })?.items)
    ? ((rawData as { items?: InventoryItem[] }).items ?? [])
    : Array.isArray(rawData)
      ? (rawData as InventoryItem[])
      : [];

  return (
    <div className="space-y-6">
      <PageHeading title="Inventory" subtitle="Search, filter, and monitor stock from live inventory endpoint." />
      <GlassCard>
        <input
          value={query}
          onChange={(event) => setQuery(event.target.value)}
          placeholder="Search products"
          className="w-full rounded-xl border border-white/10 bg-black/40 px-4 py-3 text-sm outline-none"
        />
      </GlassCard>
      {items.length === 0 ? (
        <EmptyState title="No inventory items" description="Items appear once /inventory returns data." />
      ) : (
        <GlassCard className="p-0">
          <InventoryTable items={items} />
        </GlassCard>
      )}
    </div>
  );
}
