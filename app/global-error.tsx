"use client";

export default function GlobalError() {
  return (
    <html>
      <body className="grid min-h-screen place-items-center bg-black text-white">
        <div className="rounded-3xl border border-red-400/40 bg-red-500/10 px-6 py-5">Unexpected app error occurred.</div>
      </body>
    </html>
  );
}
