<!-- [search-modal] start -->
<div class="modal fade-scale" id="searchModal" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-top modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header search-form py-0">
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
                <div class="searching-for mb-5">
                    <h4 class="fs-13 fw-normal text-gray-600 mb-3">Apa yang Anda cari?</h4>
                    <div class="row g-1" id="searchQuickLinks">
                        <div class="col-md-4 col-xl-2">
                            <a href="{{ route('dashboard.students.index') }}" class="d-block border border-dashed rounded p-2 text-center">
                                <i class="feather-users fs-5 d-block mb-1"></i>
                                <span class="fs-12 text-muted">Siswa</span>
                            </a>
                        </div>
                        <div class="col-md-4 col-xl-2">
                            <a href="{{ route('dashboard.schedules.index') }}" class="d-block border border-dashed rounded p-2 text-center">
                                <i class="feather-calendar fs-5 d-block mb-1"></i>
                                <span class="fs-12 text-muted">Jadwal</span>
                            </a>
                        </div>
                        <div class="col-md-4 col-xl-2">
                            <a href="{{ route('dashboard.classrooms.index') }}" class="d-block border border-dashed rounded p-2 text-center">
                                <i class="feather-home fs-5 d-block mb-1"></i>
                                <span class="fs-12 text-muted">Kelas</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div id="searchResults" class="d-none mt-4">
                    <h4 class="fs-13 fw-normal text-gray-600 mb-3" id="searchResultTitle">Hasil Pencarian</h4>
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
                                <a href="${item.url}" class="list-group-item list-group-item-action d-flex align-items-center px-0 border-0 mb-2">
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
