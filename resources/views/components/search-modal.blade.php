<!-- [search-modal] start -->
<div class="modal fade-scale" id="searchModal" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-top modal-dialog-scrollable">
        <div class="modal-content">
            <div class="py-0 modal-header search-form">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="feather-search fs-4 text-muted"></i>
                    </span>
                    <input type="text" class="form-control search-input-field" placeholder="Ketik untuk mencari (Siswa, Kelas, Jadwal...)" id="searchInput">
                    <span class="input-group-text">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </span>
                </div>
            </div>
            <div class="modal-body">
                <div class="mb-5 searching-for">
                    <h4 class="mb-3 text-gray-600 fs-13 fw-normal">Apa yang Anda cari?</h4>
                    <div class="row g-1" id="searchQuickLinks">
                        <div class="col-md-4 col-xl-2">
                            <a href="{{ route('dashboard.students.index') }}" class="p-2 text-center border border-dashed rounded d-block">
                                <i class="mb-1 feather-users fs-5 d-block"></i>
                                <span class="fs-12 text-muted">Siswa</span>
                            </a>
                        </div>
                      
                        <div class="col-md-4 col-xl-2">
                            <a href="{{ route('dashboard.classrooms.index') }}" class="p-2 text-center border border-dashed rounded d-block">
                                <i class="mb-1 feather-home fs-5 d-block"></i>
                                <span class="fs-12 text-muted">Kelas</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div id="searchResults" class="mt-4 d-none">
                    <h4 class="mb-3 text-gray-600 fs-13 fw-normal" id="searchResultTitle">Hasil Pencarian</h4>
                    <div class="list-group list-group-flush" id="searchList">
                        <!-- Ajax results here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('searchInput');
        const resultsDiv = document.getElementById('searchResults');
        const listContainer = document.getElementById('searchList');
        const quickLinks = document.getElementById('searchQuickLinks');
        const resultTitle = document.getElementById('searchResultTitle');
        let debounceTimer;

        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const query = this.value.trim();

            if (query.length < 2) {
                resultsDiv.classList.add('d-none');
                quickLinks.parentElement.classList.remove('d-none');
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch('{{ route("dashboard.settings.search") }}?q=' + encodeURIComponent(query))
                    .then(r => r.json())
                    .then(data => {
                        listContainer.innerHTML = '';
                        quickLinks.parentElement.classList.add('d-none');
                        resultsDiv.classList.remove('d-none');

                        if (data.results.length === 0) {
                            resultTitle.innerText = 'Tidak Ditemukan';
                            listContainer.innerHTML = `<div class="p-3 text-center text-muted">Tidak ada hasil untuk "${query}"</div>`;
                            return;
                        }

                        resultTitle.innerText = 'Hasil Pencarian';
                        data.results.forEach(item => {
                            const html = `
                                <a href="${item.url}" class="px-0 mb-2 border-0 list-group-item list-group-item-action d-flex align-items-center">
                                    <div class="avatar-text bg-soft-${item.color} text-${item.color} rounded-circle me-3">
                                        <i class="${item.icon}"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-truncate" style="max-width: 300px;">${escapeHtml(item.title)}</h6>
                                        <small class="text-muted">${item.type}</small>
                                    </div>
                                    <div class="ms-auto">
                                        <i class="feather-chevron-right text-muted"></i>
                                    </div>
                                </a>
                            `;
                            listContainer.insertAdjacentHTML('beforeend', html);
                        });
                    }).catch(console.error);
            }, 300);
        });

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.innerText = text;
            return div.innerHTML;
        }
    });
</script>
<!-- [search-modal] end -->
