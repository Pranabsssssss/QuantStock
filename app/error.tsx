"use client";

import { useEffect } from "react";
import { ErrorState } from "@/components/ui/error-state";

export default function Error({ error, reset }: { error: Error & { digest?: string }; reset: () => void }) {
  useEffect(() => {
    console.error(error);
  }, [error]);

  return (
    <div className="min-h-screen bg-black p-6">
      <div className="mx-auto max-w-3xl">
        <ErrorState message={error.message} onRetry={reset} />
      </div>
    </div>
  );
}
