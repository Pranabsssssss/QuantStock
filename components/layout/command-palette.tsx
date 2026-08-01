"use client";

import { Command } from "cmdk";
import { useRouter } from "next/navigation";

const items = [
  ["Dashboard", "/dashboard"],
  ["Inventory", "/inventory"],
  ["CSV Upload", "/upload"],
  ["AI Chat", "/chat"],
  ["Voice", "/voice"],
  ["Business Interview", "/business-interview"],
  ["Business Profile", "/business-profile"],
  ["Notifications", "/notifications"],
  ["Settings", "/settings"],
] as const;

export const CommandPalette = ({ open, setOpen }: { open: boolean; setOpen: (open: boolean) => void }) => {
  const router = useRouter();

  if (!open) return null;

  return (
    <div className="fixed inset-0 z-50 bg-black/70 p-4 backdrop-blur-sm" onClick={() => setOpen(false)}>
      <Command
        className="mx-auto mt-24 w-full max-w-xl overflow-hidden rounded-3xl border border-white/10 bg-[#090909] p-3 text-white"
        onClick={(event) => event.stopPropagation()}
      >
        <Command.Input
          placeholder="Search routes..."
          className="w-full rounded-2xl border border-white/10 bg-transparent px-4 py-3 text-sm outline-none"
        />
        <Command.List className="mt-3 max-h-80 overflow-auto">
          <Command.Empty className="px-3 py-2 text-sm text-zinc-400">No results.</Command.Empty>
          <Command.Group heading="Navigate" className="text-xs text-zinc-500">
            {items.map(([label, href]) => (
              <Command.Item
                key={href}
                className="cursor-pointer rounded-xl px-3 py-2 text-sm aria-selected:bg-white/10"
                onSelect={() => {
                  router.push(href);
                  setOpen(false);
                }}
              >
                {label}
              </Command.Item>
            ))}
          </Command.Group>
        </Command.List>
      </Command>
    </div>
  );
};
