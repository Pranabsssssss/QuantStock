"use client";

import { Line, LineChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from "recharts";
import type { ChartPoint } from "@/types/api";

export const SalesChart = ({ data }: { data: ChartPoint[] }) => {
  return (
    <div className="h-64 w-full">
      <ResponsiveContainer>
        <LineChart data={data}>
          <XAxis dataKey="label" stroke="#52525b" />
          <YAxis stroke="#52525b" />
          <Tooltip
            contentStyle={{
              background: "#0c0c0c",
              border: "1px solid rgba(255,255,255,0.1)",
              borderRadius: 12,
            }}
          />
          <Line type="monotone" dataKey="value" stroke="#ccff00" strokeWidth={2} dot={false} />
          <Line type="monotone" dataKey="secondaryValue" stroke="#10b981" strokeWidth={2} dot={false} />
        </LineChart>
      </ResponsiveContainer>
    </div>
  );
};
