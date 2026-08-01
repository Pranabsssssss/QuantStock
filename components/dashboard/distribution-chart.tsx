"use client";

import { Cell, Pie, PieChart, ResponsiveContainer, Tooltip } from "recharts";
import type { ChartPoint } from "@/types/api";

const colors = ["#ccff00", "#84cc16", "#10b981", "#facc15", "#22d3ee", "#818cf8"];

export const DistributionChart = ({ data }: { data: ChartPoint[] }) => {
  return (
    <div className="h-64 w-full">
      <ResponsiveContainer>
        <PieChart>
          <Pie data={data} dataKey="value" nameKey="label" innerRadius={52} outerRadius={80}>
            {data.map((entry, index) => (
              <Cell key={`cell-${entry.label}`} fill={colors[index % colors.length]} />
            ))}
          </Pie>
          <Tooltip
            contentStyle={{
              background: "#0c0c0c",
              border: "1px solid rgba(255,255,255,0.1)",
              borderRadius: 12,
            }}
          />
        </PieChart>
      </ResponsiveContainer>
    </div>
  );
};
