export const PageHeading = ({ title, subtitle }: { title: string; subtitle: string }) => {
  return (
    <div className="mb-6">
      <h1 className="text-3xl font-semibold tracking-[-0.05em] text-white">{title}</h1>
      <p className="mt-2 text-sm text-zinc-400">{subtitle}</p>
    </div>
  );
};
