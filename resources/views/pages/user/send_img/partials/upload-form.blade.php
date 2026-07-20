<div class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-slate-900">Upload Gambar Kegiatan</h3>
        <button id="openModalRiwayat"
            class="text-white transition-all duration-150 ease-in-out bg-blue-500 border-0 rounded-sm btn btn-md hover:bg-slate-50 hover:text-blue-500">
            Riwayat Laporan
        </button>
    </div>

    <div id="draftCardContainer"></div>

    <div class="p-4 bg-white border rounded-b-lg shadow-sm border-slate-100 sm:p-6">
        <form id="reportForm">
            @csrf
            <input type="hidden" id="reportStatus" name="status" value="0">
            <input type="hidden" id="type" name="type" value="">
            <input type="hidden" id="reportId" name="id" value="">
            <input type="hidden" name="_method" value="POST">
            <input type="hidden" id="existing_img_before" name="existing_img_before" value="">
            <input type="hidden" id="existing_img_proccess" name="existing_img_proccess" value="">
            <input type="hidden" id="existing_img_final" name="existing_img_final" value="">
            <input type="hidden" id="temp_img_before" name="temp_img_before" value="">
            <input type="hidden" id="temp_img_proccess" name="temp_img_proccess" value="">
            <input type="hidden" id="temp_img_final" name="temp_img_final" value="">

            <div class="space-y-6">
                <div>
                    <label class="block mb-3 text-sm font-medium text-slate-700 required">Gambar (Rasio 1:1)</label>
                    <div class="grid grid-cols-3 gap-2 sm:gap-3 md:gap-4">
                        @php
                            $imageConfig = [
                                ['id' => 'image1', 'name' => 'img_before', 'label' => 'Before'],
                                ['id' => 'image2', 'name' => 'img_proccess', 'label' => 'Process'],
                                ['id' => 'image3', 'name' => 'img_final', 'label' => 'After'],
                            ];

                            $acceptedTypes = '.gif,.tif,.tiff,.png,.crw,.cr2,.dng,.raf,.nef,.nrw,.orf,.rw2,.pef,.arw,.sr2,.raw,.psd,.svg,.webp,.heic,.jpg,.jpeg';

                            $uploadIcon = '<svg class="w-5 h-5 mb-1 sm:w-6 sm:h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>';

                            $deleteIcon = '<svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                        @endphp

                        @foreach ($imageConfig as $index => $config)
                            <div class="min-w-0 space-y-1.5">
                                <div class="relative">
                                    <input type="file" id="{{ $config['id'] }}" name="{{ $config['name'] }}" accept="{{ $acceptedTypes }}" class="hidden" data-upload-input="{{ $config['id'] }}">
                                    <label for="{{ $config['id'] }}"
                                        class="flex flex-col items-center justify-center w-full h-24 transition-colors border-2 border-dashed rounded-lg cursor-pointer sm:h-28 md:h-32 lg:h-36 border-slate-300 bg-slate-50 hover:bg-slate-100"
                                        data-upload-label="{{ $config['id'] }}">
                                        {!! $uploadIcon !!}
                                        <span class="text-[10px] sm:text-xs text-slate-500 text-center px-1">+ {{ $config['label'] }}</span>
                                    </label>

                                    <div id="preview{{ $index + 1 }}" class="absolute inset-0 hidden overflow-hidden rounded-lg">
                                        <img src="" alt="Preview" class="object-cover w-full h-full lazy-load">
                                        <button type="button"
                                            class="absolute p-1 sm:p-1.5 text-white transition-colors bg-red-500 rounded-full top-1 right-1 hover:bg-red-600"
                                            onclick="removeImage({{ $index + 1 }})">
                                            {!! $deleteIcon !!}
                                        </button>
                                    </div>
                                </div>

                                <div id="uploadState{{ $index + 1 }}" class="hidden upload-progress-card" data-state="idle">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center min-w-0 gap-2">
                                            <span class="hidden upload-spinner"></span>
                                            <span class="text-[10px] sm:text-xs font-medium text-slate-700 truncate upload-status">Menunggu upload</span>
                                        </div>
                                        <div class="flex items-center flex-shrink-0 gap-1.5">
                                            <span class="text-[10px] sm:text-xs font-semibold text-slate-500 upload-percent">0%</span>
                                            <button type="button"
                                                class="hidden text-[10px] sm:text-xs font-medium text-red-500 transition-colors hover:text-red-600 upload-retry"
                                                onclick="retryImageUpload({{ $index + 1 }})">
                                                Retry
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-1 upload-progress-bar"><span style="width: 0%"></span></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label for="reportArea" class="block mb-2 text-sm font-medium text-slate-700">Area</label>
                    <input type="text" id="reportArea" name="area" value="{{ old('area', $dataQr[0] ?? request('n')) }}"
                        class="w-full px-3 py-2 text-sm bg-white border rounded-lg input sm:px-4 sm:py-3 sm:text-base text-slate-900 border-slate-300 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Tulis area di sini..." required>
                </div>

                <div>
                    <label for="reportContent" class="block mb-2 text-sm font-medium text-slate-700">Keterangan Kegiatan</label>
                    <textarea id="reportContent" name="note" rows="4"
                        class="w-full px-3 py-2 text-sm bg-white border rounded-lg resize-none textarea sm:px-4 sm:py-3 sm:text-base text-slate-900 border-slate-300 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Tulis isi keterangan kegiatan di sini... (format: 'nama kegiatan')" required>{{ old('note', $dataQr[1] ?? request('keg')) }}</textarea>
                </div>

                <div class="hidden">
                    <input type="text" name="user_id" id="user_id" value="{{ auth()->user()->id }}">
                    <input type="text" name="clients_id" id="client_id" value="{{ auth()->user()->kerjasama ? auth()->user()->kerjasama->client_id : '' }}">
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
                    <button type="button" id="saveDraftBtn"
                        class="w-full sm:w-auto px-4 py-2.5 text-sm sm:text-base text-center flex items-center justify-center gap-2 font-medium text-white bg-green-500 rounded-lg hover:bg-green-600 transition-colors focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path d="M18 19H19V6.82843L17.1716 5H16V9H7V5H5V19H6V12H18V19ZM4 3H18L20.7071 5.70711C20.8946 5.89464 21 6.149 21 6.41421V20C21 20.5523 20.5523 21 20 21H4C3.44772 21 3 20.5523 3 20V4C3 3.44772 3.44772 3 4 3ZM8 14V19H16V14H8Z"></path></svg>
                        Simpan Draft
                    </button>

                    <button type="button" id="submitReportBtn"
                        class="w-full sm:w-auto px-4 py-2.5 text-sm sm:text-base text-center flex items-center justify-center gap-2 font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600 transition-colors focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path d="M4 19H20V12H22V20C22 20.5523 21.5523 21 21 21H3C2.44772 21 2 20.5523 2 20V12H4V19ZM13 9V16H11V9H6L12 3L18 9H13Z"></path></svg>
                        Kirim Laporan
                    </button>

                    <button class="hidden btn btnLoading">
                        <span class="loading loading-spinner"></span>
                        loading
                    </button>
                </div>
            </div>
        </form>
    </div>

    @include('pages.user.send_img.partials.draft-card')
</div>
