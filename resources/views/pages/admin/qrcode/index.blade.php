<x-app-layout title="Data QR Code" subtitle="Menampilkan daftar QR code yang sudah dibuat">
    @push('styles')
        <style>
            .print-action {
                min-width: 56px;
            }

            .bulk-print-toolbar {
                display: flex;
                flex-wrap: wrap;
                gap: 0.75rem;
                align-items: center;
                justify-content: space-between;
            }
        </style>
    @endpush

    <div class="flex min-h-screen pb-28 admin-shell bg-slate-50">
        @include('components.sidebar-component')

        <div class="flex-1 p-6 overflow-y-auto admin-content">
            <div class="flex flex-col gap-4 p-4 mb-6 admin-filter-card md:flex-row md:items-center md:justify-between">
                <form method="GET" action="{{ route('admin-qrcode.index') }}" class="flex flex-col gap-3 sm:flex-row">
                    <label class="flex items-center w-full max-w-xl gap-2 bg-white input input-bordered">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4 opacity-70">
                            <path fill-rule="evenodd" d="M9.965 11.026a5.5 5.5 0 1 1 1.06-1.06l3.755 3.754a.75.75 0 1 1-1.06 1.06l-3.755-3.754ZM10.5 6.5a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd" />
                        </svg>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            class="grow"
                            placeholder="Cari berdasarkan data QR" />
                    </label>

                    <div class="flex gap-2">
                        <button type="submit" class="text-blue-500 uppercase transition-all duration-200 ease-in-out border-none rounded-sm btn btn-sm bg-blue-500/20 hover:bg-blue-500 hover:text-white">
                            Search
                        </button>

                        @if (request()->filled('search'))
                            <a href="{{ route('admin-qrcode.index') }}" class="text-red-500 uppercase transition-all duration-200 ease-in-out border-none rounded-sm btn btn-sm bg-red-500/20 hover:bg-red-500 hover:text-white">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>

                <a href="{{ route('admin-qrcode.create') }}" class="text-blue-500 uppercase transition-all duration-200 ease-in-out border-none rounded-sm btn btn-sm bg-blue-500/20 hover:bg-blue-500 hover:text-white">
                    <i class="ri-add-line"></i> Add QR Code
                </a>
            </div>

            <div class="p-4 mb-4 admin-filter-card bulk-print-toolbar">
                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="selectAllQr" class="checkbox checkbox-sm checkbox-primary">
                        <span class="text-sm text-slate-700">Pilih Semua di halaman ini</span>
                    </label>
                    <span id="selectedQrCount" class="text-sm text-slate-500">0 data dipilih</span>
                </div>

                <div class="flex gap-2">
                    <button
                        type="button"
                        id="printSelectedBtn"
                        class="uppercase transition-all duration-200 ease-in-out border-none rounded-sm text-sky-600 btn btn-sm bg-sky-500/20 hover:bg-sky-500 hover:text-white">
                        Print Selected
                    </button>
                    <button
                        type="button"
                        id="printAllOnPageBtn"
                        class="text-indigo-600 uppercase transition-all duration-200 ease-in-out border-none rounded-sm btn btn-sm bg-indigo-500/20 hover:bg-indigo-500 hover:text-white">
                        Print Semua Halaman Ini
                    </button>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-6 shadow-sm alert alert-success">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white shadow-lg card admin-panel">
                <div class="card-body">
                    <div class="overflow-x-auto">
                        <table class="table w-full table-zebra">
                            <thead>
                                <tr class="">
                                    <th class="w-12 text-center">
                                        <input type="checkbox" id="selectAllTableQr" class="checkbox checkbox-sm checkbox-primary">
                                    </th>
                                    <th class="w-16">No</th>
                                    <th class="w-40">Preview QR</th>
                                    <th>Data</th>
                                    <th>Link Redirect</th>
                                    <th class="w-40 text-center">Dibuat</th>
                                    <th class="w-32 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($qrCodes as $index => $qrCode)
                                    @php
                                        if($qrCode->created_at->format('Y-m-d') <= '2026-04-29') {
                                            $targetUrl = \App\Services\Media\QrCodeService::buildTargetUrlFromStoredData($qrCode->data);
                                        } else {
                                            $targetUrl = 'https://laporan-sac.sac-po.com/send-img/laporan?id=' . $qrCode->id;
                                        }
                                    @endphp
                                    <tr class="hover">
                                        <td class="text-center">
                                            <input
                                                type="checkbox"
                                                class="checkbox checkbox-sm checkbox-primary qr-select-item"
                                                value="{{ $qrCode->id }}"
                                                data-image-url="{{ \App\Helpers\FileHelper::getImageUrl($qrCode->qr) }}"
                                                data-data="{{ $qrCode->data }}"
                                                data-target-url="{{ $targetUrl }}">
                                        </td>
                                        <td>{{ $qrCodes->firstItem() + $index }}</td>
                                        <td>
                                            <div class="flex items-center justify-center p-2 bg-white border rounded-lg border-slate-200 w-28 h-28">
                                                <img
                                                    src="{{ \App\Helpers\FileHelper::getImageUrl($qrCode->qr) }}"
                                                    alt="QR Code {{ $qrCodes->firstItem() + $index }}"
                                                    class="object-contain w-24 h-24">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="max-w-xl text-sm text-slate-600">
                                                {{ \Illuminate\Support\Str::limit($qrCode->data, 120) }}
                                            </div>
                                        </td>
                                        <td>
                                            <a
                                                href="{{ $targetUrl }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="block max-w-sm text-sm font-medium text-blue-600 break-all transition hover:text-blue-700 hover:underline">
                                                {{ $targetUrl }}
                                            </a>
                                        </td>
                                        <td class="text-sm text-center text-slate-500">
                                            {{ $qrCode->created_at?->format('d M Y') }}
                                        </td>
                                        <td>
                                            <div class="flex justify-center gap-2">
                                                <a
                                                    href="{{ route('admin-qrcode.edit', $qrCode->id) }}"
                                                    class="uppercase transition-all duration-200 ease-in-out border-none rounded-sm text-amber-600 btn btn-xs bg-amber-500/20 hover:bg-amber-500 hover:text-white">
                                                    Edit
                                                </a>
                                                <form action="{{ route('admin-qrcode.destroy', $qrCode->id) }}" method="POST" onsubmit="return confirm('Hapus QR code ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 uppercase transition-all duration-200 ease-in-out border-none rounded-sm btn btn-xs bg-red-500/20 hover:bg-red-500 hover:text-white">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-10 text-center text-slate-500">
                                            {{ request()->filled('search') ? 'Data QR code tidak ditemukan untuk pencarian tersebut.' : 'Belum ada data QR code.' }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($qrCodes->total() > 0)
                        <div class="flex flex-col gap-3 pt-2 mt-2 border-t border-slate-100 md:flex-row md:items-center md:justify-between">
                            <p class="text-sm text-slate-500">
                                Menampilkan
                                <span class="font-semibold text-slate-700">{{ $qrCodes->firstItem() }}</span>
                                -
                                <span class="font-semibold text-slate-700">{{ $qrCodes->lastItem() }}</span>
                                dari
                                <span class="font-semibold text-slate-700">{{ $qrCodes->total() }}</span>
                                data
                            </p>

                            @if ($qrCodes->hasPages())
                                <nav class="flex flex-wrap items-center gap-1" aria-label="Pagination QR Code">
                                    @if ($qrCodes->onFirstPage())
                                        <span class="btn btn-sm btn-disabled">
                                            <i class="ri-arrow-left-s-line"></i>
                                        </span>
                                    @else
                                        <a href="{{ $qrCodes->previousPageUrl() }}" class="btn btn-sm">
                                            <i class="ri-arrow-left-s-line"></i>
                                        </a>
                                    @endif

                                    @foreach ($qrCodes->getUrlRange(max(1, $qrCodes->currentPage() - 2), min($qrCodes->lastPage(), $qrCodes->currentPage() + 2)) as $page => $url)
                                        <a
                                            href="{{ $url }}"
                                            class="btn btn-sm {{ $page === $qrCodes->currentPage() ? 'btn-active bg-blue-600 text-white hover:bg-blue-700' : '' }}">
                                            {{ $page }}
                                        </a>
                                    @endforeach

                                    @if ($qrCodes->hasMorePages())
                                        <a href="{{ $qrCodes->nextPageUrl() }}" class="btn btn-sm">
                                            <i class="ri-arrow-right-s-line"></i>
                                        </a>
                                    @else
                                        <span class="btn btn-sm btn-disabled">
                                            <i class="ri-arrow-right-s-line"></i>
                                        </span>
                                    @endif
                                </nav>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function escapeHtml(value) {
                return String(value)
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function buildQrItems(items) {
                return items.map((item) => `
                    <div class="qr-item">
                        <div class="qr-image-wrapper">
                            <img src="${escapeHtml(item.imageUrl)}" alt="QR Code" class="qr-image">
                        </div>
                        <div class="qr-label">QR Kegiatan</div>
                        <div class="qr-data">${escapeHtml(item.data)}</div>
                    </div>
                `).join('');
            }

            function printQrCodes(items) {
                if (!items.length) {
                    alert('Pilih minimal 1 QR code untuk dicetak.');
                    return;
                }

                const printWindow = window.open('', '_blank', 'width=900,height=700');

                if (!printWindow) {
                    alert('Popup diblokir browser. Izinkan popup untuk mencetak QR code.');
                    return;
                }

                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html lang="id">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Print QR Code</title>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                margin: 0;
                                padding: 32px;
                                color: #0f172a;
                                background: #ffffff;
                            }

                            .print-card {
                                max-width: 1100px;
                                margin: 0 auto;
                                text-align: center;
                            }

                            .print-title {
                                font-size: 26px;
                                font-weight: 700;
                                margin-bottom: 8px;
                            }

                            .print-subtitle {
                                font-size: 14px;
                                color: #475569;
                                margin-bottom: 28px;
                            }

                            .qr-grid {
                                display: grid;
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                                gap: 24px;
                            }

                            .qr-item {
                                border: 1px solid #e2e8f0;
                                border-radius: 12px;
                                padding: 22px;
                                break-inside: avoid;
                                page-break-inside: avoid;
                            }

                            .qr-image-wrapper {
                                display: flex;
                                justify-content: center;
                                align-items: center;
                                margin-bottom: 20px;
                            }

                            .qr-image {
                                width: 220px;
                                height: 220px;
                                object-fit: contain;
                            }

                            .qr-label {
                                font-size: 12px;
                                font-weight: 700;
                                letter-spacing: 0.08em;
                                text-transform: uppercase;
                                color: #64748b;
                                margin-bottom: 8px;
                            }

                            .qr-data {
                                font-size: 20px;
                                font-weight: 700;
                                margin-bottom: 18px;
                                word-break: break-word;
                            }

                            .qr-url {
                                font-size: 13px;
                                color: #334155;
                                word-break: break-word;
                                line-height: 1.6;
                            }

                            @media print {
                                body {
                                    padding: 18px;
                                }

                                .qr-grid {
                                    grid-template-columns: repeat(2, minmax(0, 1fr));
                                }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="print-card">
                            <div class="print-title">QR Code Laporan</div>
                            <div class="print-subtitle">Scan QR untuk membuka halaman upload laporan</div>
                            <div class="qr-grid">
                                ${buildQrItems(items)}
                            </div>
                        </div>
                        <script>
                            window.onload = function () {
                                window.print();
                                window.onafterprint = function () {
                                    window.close();
                                };
                            };
                        <\/script>
                    </body>
                    </html>
                `);

                printWindow.document.close();
            }

            function getSelectedQrItems() {
                return Array.from(document.querySelectorAll('.qr-select-item:checked')).map((item) => ({
                    id: item.value,
                    imageUrl: item.dataset.imageUrl,
                    data: item.dataset.data,
                    targetUrl: item.dataset.targetUrl,
                }));
            }

            function getAllQrItemsOnPage() {
                return Array.from(document.querySelectorAll('.qr-select-item')).map((item) => ({
                    id: item.value,
                    imageUrl: item.dataset.imageUrl,
                    data: item.dataset.data,
                    targetUrl: item.dataset.targetUrl,
                }));
            }

            function syncSelectAllState() {
                const items = Array.from(document.querySelectorAll('.qr-select-item'));
                const checkedItems = items.filter((item) => item.checked);
                const allChecked = items.length > 0 && checkedItems.length === items.length;
                const partialChecked = checkedItems.length > 0 && checkedItems.length < items.length;
                const countLabel = document.getElementById('selectedQrCount');
                const topSelectAll = document.getElementById('selectAllQr');
                const tableSelectAll = document.getElementById('selectAllTableQr');

                if (countLabel) {
                    countLabel.textContent = `${checkedItems.length} data dipilih`;
                }

                if (topSelectAll) {
                    topSelectAll.checked = allChecked;
                    topSelectAll.indeterminate = partialChecked;
                }

                if (tableSelectAll) {
                    tableSelectAll.checked = allChecked;
                    tableSelectAll.indeterminate = partialChecked;
                }
            }

            function setAllQrSelection(checked) {
                document.querySelectorAll('.qr-select-item').forEach((item) => {
                    item.checked = checked;
                });

                syncSelectAllState();
            }

            document.addEventListener('DOMContentLoaded', function () {
                const topSelectAll = document.getElementById('selectAllQr');
                const tableSelectAll = document.getElementById('selectAllTableQr');
                const printSelectedBtn = document.getElementById('printSelectedBtn');
                const printAllOnPageBtn = document.getElementById('printAllOnPageBtn');

                topSelectAll?.addEventListener('change', function () {
                    setAllQrSelection(this.checked);
                });

                tableSelectAll?.addEventListener('change', function () {
                    setAllQrSelection(this.checked);
                });

                document.querySelectorAll('.qr-select-item').forEach((item) => {
                    item.addEventListener('change', syncSelectAllState);
                });

                printSelectedBtn?.addEventListener('click', function () {
                    printQrCodes(getSelectedQrItems());
                });

                printAllOnPageBtn?.addEventListener('click', function () {
                    printQrCodes(getAllQrItemsOnPage());
                });

                syncSelectAllState();
            });
        </script>
    @endpush
</x-app-layout>
