---
paths:
  - 'resources/views/layouts/**'
---

# Layouts

## Public layout for guest Livewire pages
layouts/public.blade.php is for guest-accessible Livewire pages that cannot use layouts/app (app sidebar requires auth()->user()). Prefer layouts::public for guests; keep layouts::app for authenticated chrome.
