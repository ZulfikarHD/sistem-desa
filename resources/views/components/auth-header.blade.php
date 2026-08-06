@props([
    'title',
    'description',
])

<div class="flex w-full flex-col gap-1 text-start">
    <flux:heading size="xl" class="!font-display !text-brand-forest">{{ $title }}</flux:heading>
    <flux:subheading class="!text-brand-ink/70">{{ $description }}</flux:subheading>
</div>
