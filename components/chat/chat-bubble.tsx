import { cn } from "@/lib/cn";

export const ChatBubble = ({ role, content }: { role: "user" | "assistant"; content: string }) => {
  return (
    <div className={cn("flex", role === "user" ? "justify-end" : "justify-start")}>
      <div
        className={cn(
          "max-w-[75%] rounded-2xl border px-4 py-3 text-sm",
          role === "user"
            ? "border-lime-300/40 bg-lime-300/10 text-lime-100"
            : "border-white/10 bg-white/[0.03] text-zinc-100",
        )}
      >
        {content}
      </div>
    </div>
  );
};
