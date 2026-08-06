<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-6">
    <div>
        <flux:heading size="xl" data-test="scan-qr-heading">
            {{ __('Scan QR Pengambilan') }}
        </flux:heading>
        <flux:text class="mt-1">
            {{ __('Scan QR pada surat warga saat pengambilan. QR hanya berlaku sekali — setelah sukses, status menjadi selesai dan QR tidak dapat dipakai ulang.') }}
        </flux:text>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Kamera (BarcodeDetector + getUserMedia; fallback ke input manual) --}}
        <div
            class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"
            data-test="scan-qr-camera-panel"
            x-data="{
                scanning: false,
                cameraError: '',
                stream: null,
                detector: null,
                rafId: null,
                async startCamera() {
                    this.cameraError = '';
                    if (! window.isSecureContext && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                        this.cameraError = 'Kamera memerlukan HTTPS atau localhost.';
                        return;
                    }
                    if (! ('BarcodeDetector' in window)) {
                        this.cameraError = 'Browser tidak mendukung pemindai QR. Gunakan input token manual.';
                        return;
                    }
                    try {
                        this.detector = new BarcodeDetector({ formats: ['qr_code'] });
                        this.stream = await navigator.mediaDevices.getUserMedia({
                            video: { facingMode: 'environment' },
                            audio: false,
                        });
                        this.$refs.video.srcObject = this.stream;
                        await this.$refs.video.play();
                        this.scanning = true;
                        this.tick();
                    } catch (e) {
                        this.cameraError = 'Tidak dapat mengakses kamera. Izinkan akses kamera atau isi token manual.';
                        this.stopCamera();
                    }
                },
                async tick() {
                    if (! this.scanning || ! this.detector) {
                        return;
                    }
                    try {
                        const codes = await this.detector.detect(this.$refs.video);
                        if (codes.length > 0 && codes[0].rawValue) {
                            const token = codes[0].rawValue;
                            this.stopCamera();
                            await $wire.prosesScan(token);
                            return;
                        }
                    } catch (e) {
                        // lanjut frame berikutnya
                    }
                    this.rafId = requestAnimationFrame(() => this.tick());
                },
                stopCamera() {
                    this.scanning = false;
                    if (this.rafId) {
                        cancelAnimationFrame(this.rafId);
                        this.rafId = null;
                    }
                    if (this.stream) {
                        this.stream.getTracks().forEach((t) => t.stop());
                        this.stream = null;
                    }
                    if (this.$refs.video) {
                        this.$refs.video.srcObject = null;
                    }
                },
                destroy() {
                    this.stopCamera();
                }
            }"
            x-on:livewire:navigating.window="stopCamera()"
        >
            <flux:heading size="sm">{{ __('Kamera') }}</flux:heading>
            <flux:text class="mt-1 text-sm">
                {{ __('Arahkan kamera ke QR code pada surat. Jika kamera tidak tersedia, gunakan input manual.') }}
            </flux:text>

            <div class="mt-4 overflow-hidden rounded-lg bg-zinc-900 aspect-video">
                <video
                    x-ref="video"
                    class="h-full w-full object-cover"
                    playsinline
                    muted
                    data-test="scan-qr-video"
                ></video>
            </div>

            <p
                x-show="cameraError"
                x-text="cameraError"
                class="mt-2 text-sm text-red-600 dark:text-red-400"
                data-test="scan-qr-camera-error"
            ></p>

            <div class="mt-4 flex flex-wrap gap-2">
                <flux:button
                    type="button"
                    variant="primary"
                    x-show="!scanning"
                    x-on:click="startCamera()"
                    data-test="scan-qr-start-camera"
                >
                    {{ __('Mulai Kamera') }}
                </flux:button>
                <flux:button
                    type="button"
                    variant="ghost"
                    x-show="scanning"
                    x-on:click="stopCamera()"
                    data-test="scan-qr-stop-camera"
                >
                    {{ __('Stop Kamera') }}
                </flux:button>
            </div>
        </div>

        {{-- Input token manual --}}
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700" data-test="scan-qr-manual-panel">
            <flux:heading size="sm">{{ __('Input Token Manual') }}</flux:heading>
            <flux:text class="mt-1 text-sm">
                {{ __('Tempel atau ketik token QR jika kamera tidak dipakai.') }}
            </flux:text>

            <form wire:submit="prosesScan" class="mt-4 space-y-4">
                <flux:field>
                    <flux:label>{{ __('Token QR') }}</flux:label>
                    <flux:input
                        wire:model="qrToken"
                        type="text"
                        maxlength="64"
                        autocomplete="off"
                        placeholder="{{ __('Tempel token QR di sini') }}"
                        data-test="scan-qr-token-input"
                    />
                    <flux:error name="qrToken" />
                </flux:field>

                <flux:button
                    type="submit"
                    variant="primary"
                    wire:loading.attr="disabled"
                    data-test="scan-qr-submit"
                >
                    {{ __('Proses Scan') }}
                </flux:button>
            </form>

            @if ($hasilPesan)
                <div
                    class="mt-4 rounded-lg border p-3 text-sm {{ $hasilSukses ? 'border-green-300 bg-green-50 text-green-800 dark:border-green-700 dark:bg-green-950 dark:text-green-200' : 'border-red-300 bg-red-50 text-red-800 dark:border-red-700 dark:bg-red-950 dark:text-red-200' }}"
                    data-test="scan-qr-result"
                    data-success="{{ $hasilSukses ? '1' : '0' }}"
                >
                    {{ $hasilPesan }}
                </div>
            @endif
        </div>
    </div>
</div>
