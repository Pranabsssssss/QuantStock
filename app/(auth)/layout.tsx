export default function AuthLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="relative min-h-screen bg-black px-4 py-8">
      <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(204,255,0,0.15),transparent_40%),radial-gradient(circle_at_80%_80%,rgba(16,185,129,0.15),transparent_35%)]" />
      <div className="relative mx-auto flex min-h-[85vh] max-w-md items-center">{children}</div>
    </div>
  );
}
