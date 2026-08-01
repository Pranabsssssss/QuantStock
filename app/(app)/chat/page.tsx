"use client";

import { useState } from "react";
import { useMutation } from "@tanstack/react-query";
import { ChatBubble } from "@/components/chat/chat-bubble";
import { EmptyState } from "@/components/ui/empty-state";
import { GlassCard } from "@/components/ui/glass-card";
import { GradientButton } from "@/components/ui/gradient-button";
import { PageHeading } from "@/components/ui/page-heading";
import { getErrorMessage } from "@/lib/error-message";
import { chatService } from "@/services/chat.service";
import { toast } from "sonner";

interface Message {
  role: "user" | "assistant";
  content: string;
}

export default function ChatPage() {
  const [input, setInput] = useState("");
  const [messages, setMessages] = useState<Message[]>([]);

  const mutation = useMutation({
    mutationFn: chatService.sendMessage,
    onSuccess: (data, variables) => {
      setMessages((prev) => [...prev, { role: "user", content: variables.message }, { role: "assistant", content: data.message ?? "" }]);
      setInput("");
    },
    onError: (error) => toast.error(getErrorMessage(error, "Chat request failed")),
  });

  return (
    <div className="space-y-6">
      <PageHeading title="AI Chat" subtitle="Send messages to /chat and view backend responses." />
      <GlassCard className="space-y-4">
        <div className="max-h-[60vh] space-y-3 overflow-auto">
          {messages.length === 0 ? <EmptyState title="No chat history" description="Start a conversation to populate live responses." /> : messages.map((m, i) => <ChatBubble key={i} role={m.role} content={m.content} />)}
        </div>
        <div className="flex gap-2">
          <input
            value={input}
            onChange={(event) => setInput(event.target.value)}
            placeholder="Ask anything about your business..."
            className="flex-1 rounded-xl border border-white/10 bg-black/40 px-4 py-3 text-sm outline-none"
          />
          <GradientButton disabled={!input.trim()} loading={mutation.isPending} onClick={() => mutation.mutate({ message: input })}>Send</GradientButton>
        </div>
      </GlassCard>
    </div>
  );
}
