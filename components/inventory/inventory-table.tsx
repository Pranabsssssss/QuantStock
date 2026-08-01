import type { InventoryItem } from "@/types/api";

export const InventoryTable = ({ items }: { items: InventoryItem[] }) => {
  return (
    <div className="overflow-hidden rounded-3xl border border-white/10">
      <table className="w-full text-left text-sm">
        <thead className="bg-white/5 text-zinc-400">
          <tr>
            <th className="px-4 py-3">Product</th>
            <th className="px-4 py-3">Category</th>
            <th className="px-4 py-3">Stock</th>
            <th className="px-4 py-3">Min Stock</th>
            <th className="px-4 py-3">Status</th>
            <th className="px-4 py-3">Forecast</th>
          </tr>
        </thead>
        <tbody>
          {items.map((item) => (
            <tr key={String(item.id)} className="border-t border-white/5 text-zinc-200">
              <td className="px-4 py-3">{item.name}</td>
              <td className="px-4 py-3">{item.category ?? "-"}</td>
              <td className="px-4 py-3">{item.stock ?? "-"}</td>
              <td className="px-4 py-3">{item.min_stock ?? "-"}</td>
              <td className="px-4 py-3">{item.status ?? "-"}</td>
              <td className="px-4 py-3">{item.forecast_status ?? "-"}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};
