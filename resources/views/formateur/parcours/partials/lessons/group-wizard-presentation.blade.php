@php
    $presentationPageCount = count($customPresentation['pages'] ?? []);
@endphp

<div
    x-data="{
        page: 1,
        pageCount: {{ $presentationPageCount }},
        next() {
            if (this.page < this.pageCount) {
                this.page += 1;
            }
        },
        previous() {
            if (this.page > 1) {
                this.page -= 1;
            }
        }
    }"
    class="h-full"
>
    <article class="mx-auto flex h-full max-w-6xl flex-col overflow-hidden rounded-[28px] border border-gray-100 bg-white shadow-sm">
        <div class="flex-1 overflow-hidden bg-white">
            @for ($pageIndex = 1; $pageIndex <= $presentationPageCount; $pageIndex++)
                <section
                    x-show="page === {{ $pageIndex }}"
                    x-cloak
                    class="h-full w-full bg-white"
                    style="display: none;"
                ></section>
            @endfor
        </div>

        <div class="border-t border-gray-100 bg-white px-6 py-4 md:px-8">
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-[140px]">
                    <button
                        type="button"
                        x-show="page > 1"
                        x-cloak
                        @click="previous()"
                        class="inline-flex items-center rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-orangeone hover:text-orangeone"
                        style="display: none;"
                    >
                        Precedent
                    </button>
                </div>

                <div class="flex min-w-[140px] justify-end">
                    <button
                        type="button"
                        x-show="page < pageCount"
                        x-cloak
                        @click="next()"
                        class="btn-oneduc !rounded-full !px-6 !py-3 !text-sm"
                        style="display: none;"
                    >
                        Suivant
                    </button>
                </div>
            </div>
        </div>
    </article>
</div>
