<div id="property-detail-modal"
     class="hidden fixed inset-0 z-50 overflow-y-auto"
     role="dialog"
     aria-modal="true"
     aria-labelledby="property-detail-modal-title">
    <div class="fixed inset-0 bg-slate-900/50 js-property-modal-backdrop" aria-hidden="true"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-[44rem] bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-3 border-b border-slate-200 bg-white sticky top-0 z-10">
                <h2 id="property-detail-modal-title" class="font-semibold text-lg text-slate-900">Property Details</h2>
                <button type="button"
                        class="p-2 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded js-property-modal-close"
                        aria-label="Close">
                    <span aria-hidden="true" class="text-xl leading-none">&times;</span>
                </button>
            </div>
            <div id="property-detail-modal-body" class="max-h-[calc(100vh-8rem)] overflow-y-auto">
                <p class="px-6 py-8 text-sm text-slate-500 text-center js-property-modal-loading hidden">Loading&hellip;</p>
            </div>
        </div>
    </div>
</div>
