@extends('layouts.app')

@section('title', 'WhatsApp Chat')

@section('chat-app')
    <div class="whatsapp-chat-app-container">
        <div class="main-content d-flex">
            <!-- Sidebar -->
            <div class="content-sidebar content-sidebar-xl">
                <div class="bg-white content-sidebar-header sticky-top hstack justify-content-between">
                    <h4 class="mb-0 fw-bolder">Chat</h4>
                    <a href="javascript:void(0);" class="app-sidebar-close-trigger d-flex d-lg-none">
                        <i class="feather-x"></i>
                    </a>
                </div>
                <div class="content-sidebar-body">
                    <div class="px-4 py-0 d-flex align-items-center justify-content-between border-bottom">
                        <div class="sidebar-search w-100">
                            <input type="search" class="px-0 py-3 bg-transparent border-0 w-100" id="conversationSearch"
                                placeholder="Search conversations..." autocomplete="off">
                        </div>
                    </div>
                    <div class="content-sidebar-items" id="conversationList">
                        @forelse($conversations as $chatConversation)
                            <div class="p-4 d-flex position-relative border-bottom c-pointer single-item {{ isset($conversation) && $conversation?->id == $chatConversation->id ? 'active' : '' }}"
                                data-id="{{ $chatConversation->id }}"
                                onclick="loadConversation('{{ $chatConversation->id }}')">
                                <div class="avatar-image">
                                    <div class="text-white avatar-text">
                                        {{ strtoupper(substr($chatConversation->profile_name, 0, 1)) }}
                                    </div>
                                </div>
                                <div class="ms-3 item-desc flex-grow-1">
                                    <div class="w-100 d-flex align-items-center justify-content-between">
                                        <div class="gap-2 hstack me-2">
                                            <span class="fw-bold text-dark">{{ $chatConversation->profile_name }}</span>
                                        </div>
                                        <span class="fs-10 fw-medium text-muted text-uppercase">{{ $chatConversation->updated_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="mt-1 mb-0 fs-12 text-muted text-truncate">
                                        {{ $chatConversation->messages()->latest()->first()->content ?? 'No messages' }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted">No conversations found.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Main Chat Area -->
            <div class="content-area" id="chatPaneContainer">
                @if (isset($conversation) && isset($messages))
                    @include('dashboard.whatsapp-chat.partials.chat-pane', [
                        'conversation' => $conversation,
                        'messages' => $messages,
                        'templates' => $templates,
                    ])
                @else
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <div class="text-center">
                            <i class="mb-3 feather-message-square fs-1 text-muted d-block"></i>
                            <p class="text-muted">Select a conversation to start chatting.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>    <script>
        let currentConvId = '{{ isset($conversation) ? $conversation->id : '' }}';

        async function loadConversation(id) {
            if (id === currentConvId) return;

            const container = document.getElementById('chatPaneContainer');
            const items = document.querySelectorAll('.single-item');

            // UI Feedback: Set active in sidebar immediately
            items.forEach(item => {
                item.classList.remove('active');
                if (item.dataset.id === id) item.classList.add('active');
            });

            // Show loading overlay
            container.innerHTML = `
                <div class="bg-white opacity-75 d-flex align-items-center justify-content-center h-100" style="z-index: 100;">
                    <div class="text-center">
                        <div class="mb-3 spinner-border text-primary" role="status"></div>
                        <p class="text-muted fw-bold">Memuat percakapan...</p>
                    </div>
                </div>
            `;

            try {
                const response = await fetch(`/dashboard/whatsapp-chat/${id}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Gagal memuat percakapan');

                const html = await response.text();
                container.innerHTML = html;
                currentConvId = id;

                // Update URL without reload
                history.pushState(null, '', `/dashboard/whatsapp-chat/${id}`);

                // Re-initialize scripts in the partial (the script inside chat-pane.blade.php will auto-run)
                // If there are tooltips or other global components, re-init them here
                if (window.bootstrap && window.bootstrap.Tooltip) {
                    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                }

                // Scroll to bottom
                const messagesBody = document.getElementById('messagesContainer');
                if (messagesBody) {
                    setTimeout(() => {
                        messagesBody.scrollTop = messagesBody.scrollHeight;
                    }, 100);
                }

            } catch (error) {
                console.error(error);
                Swal.fire('Error', error.message, 'error');
                container.innerHTML = `
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <div class="text-center">
                            <i class="mb-3 feather-alert-circle fs-1 text-danger d-block"></i>
                            <p class="text-danger">${error.message}</p>
                            <button onclick="loadConversation('${id}')" class="mt-2 btn btn-primary btn-sm">Coba Lagi</button>
                        </div>
                    </div>
                `;
            }
        }

        // Handle browser back/forward buttons
        window.onpopstate = function() {
            const pathParts = window.location.pathname.split('/');
            const id = pathParts[pathParts.length - 1];
            if (id && id !== 'whatsapp-chat') {
                loadConversation(id);
            }
        };

        // Event delegation for Edit and Delete
        document.addEventListener('click', function(e) {
            // Edit
            const editBtn = e.target.closest('.edit-btn');
            if (editBtn) {
                const id = editBtn.dataset.messageId;
                const content = editBtn.dataset.content;
                const modal = document.getElementById('editMessageModal');
                const form = document.getElementById('editMessageForm');
                const input = document.getElementById('editContentInput');

                input.value = content;
                form.action = `/dashboard/whatsapp-chat/messages/${id}/edit`;

                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            }

            // Delete
            const deleteBtn = e.target.closest('.delete-btn');
            if (deleteBtn) {
                const id = deleteBtn.dataset.messageId;
                Swal.fire({
                    title: 'Hapus Pesan?',
                    text: "Tindakan ini tidak dapat dibatalkan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/dashboard/whatsapp-chat/messages/${id}/delete`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        }).then(res => res.json()).then(data => {
                            if (data.success) {
                                Swal.fire('Terhapus!', 'Pesan telah dihapus.', 'success').then(() => {
                                    // Smoothly remove message from UI
                                    const item = deleteBtn.closest('.single-chat-item');
                                    item.style.opacity = '0';
                                    setTimeout(() => item.remove(), 300);
                                });
                            } else {
                                Swal.fire('Gagal!', data.message || 'Gagal menghapus pesan', 'error');
                            }
                        });
                    }
                });
            }

            // Reaction (Open Modal)
            const reactBtn = e.target.closest('.reaction-btn');
            if (reactBtn) {
                const msgId = reactBtn.dataset.messageId;
                const modal = document.getElementById('reactionModal');
                modal.dataset.currentMessageId = msgId;
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            }
        });

        // Search conversations
        document.getElementById('conversationSearch')?.addEventListener('input', function(e) {
            const q = e.target.value.toLowerCase();
            document.querySelectorAll('.single-item').forEach(item => {
                const name = item.querySelector('.fw-bold').textContent.toLowerCase();
                const lastMsg = item.querySelector('.text-muted').textContent.toLowerCase();
                if (name.includes(q) || lastMsg.includes(q)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>
@endsection

@section('modal')
    <!-- Edit Message Modal -->
    <div class="modal fade" id="editMessageModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="border-0 shadow-lg modal-content">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">Edit Pesan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editMessageForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <textarea name="content" id="editContentInput" class="form-control bg-body-secondary border-subtle" rows="4" required></textarea>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="px-4 btn btn-light rounded-pill" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="px-4 btn btn-primary rounded-pill">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reaction Picker Modal -->
    <div class="modal fade" id="reactionModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="border-0 shadow-lg modal-content rounded-4">
                <div class="p-4 modal-body">
                    <h6 class="mb-3 text-center fw-bold">Pilih Reaksi</h6>
                    <div class="flex-wrap gap-2 d-flex justify-content-center">
                        @php $emojis = ['👍', '❤️', '😂', '😮', '😢', '🙏', '🔥', '✅']; @endphp
                        @foreach($emojis as $emoji)
                            <button class="p-2 btn btn-light fs-4 emoji-select-btn" onclick="submitReaction('{{ $emoji }}')">{{ $emoji }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function submitReaction(emoji) {
            const modalEl = document.getElementById('reactionModal');
            const msgId = modalEl.dataset.currentMessageId;
            const bsModal = bootstrap.Modal.getInstance(modalEl);

            fetch(`/dashboard/whatsapp-chat/messages/${msgId}/react`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ emoji }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    bsModal.hide();
                    // Smoothly refresh the current conversation pane
                    if (currentConvId) {
                        fetch(`/dashboard/whatsapp-chat/${currentConvId}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(res => res.text())
                        .then(html => {
                            document.getElementById('chatPaneContainer').innerHTML = html;
                        });
                    }
                } else {
                    Swal.fire('Error', 'Gagal memberikan reaksi', 'error');
                }
            });
        }
    </script>
@endsection

    @push('css')
        <style>
            /* [ Layout Locking ] */
            /* Force the main container to fill exactly 100% of the viewport height */
            html, body {
                height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important; /* Lock global scroll */
            }

            /* Hide the global header/footer if they interfere with the chat-app layout */
            /* .nxl-header, .nxl-footer, .footer {
                display: none !important;
            } */

            .nxl-container {
                height: 100vh !important;
                overflow: hidden !important;
            }

            .nxl-content {
                height: 100vh !important;
                padding: 0 !important;
                margin: 0 !important;
                overflow: hidden !important;
            }

            .main-content {
                height: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            /* [ WhatsApp Chat Container ] */
            .whatsapp-chat-app-container {
                height: 100vh;
                display: flex;
                flex-direction: column;
                overflow: hidden;
                background: #fff;
            }

            .whatsapp-chat-app-container .main-content {
                flex: 1;
                display: flex;
                height: 100%;
                min-height: 0;
            }

            /* [ Sidebar Independent Scroll ] */
            .whatsapp-chat-app-container .content-sidebar {
                width: 350px;
                height: 100%;
                display: flex;
                flex-direction: column;
                border-right: 1px solid #f3f4f6;
                flex-shrink: 0;
            }

            .whatsapp-chat-app-container .content-sidebar-body {
                flex: 1;
                overflow-y: auto;
                scrollbar-width: thin;
            }

            /* [ Chat Area Independent Scroll ] */
            .whatsapp-chat-app-container .content-area {
                flex: 1;
                height: 100%;
                display: flex;
                flex-direction: column;
                min-width: 0;
                /* background: #f9fafb; */
            }

            .whatsapp-chat-app-container .content-area-body {
                flex: 1;
                overflow-y: auto;
                padding: 20px 30px;
                scrollbar-width: thin;
                background-image: radial-gradient(#d1d5db 0.5px, transparent 0.5px);
                background-size: 20px 20px;
                display: flex;
                flex-direction: column;
            }

            /* [ Bubble Styling ] */
            .chat-bubble-container {
                max-width: 70%;
                margin-bottom: 5px;
            }

            .chat-bubble {
                padding: 10px 16px;
                border-radius: 14px;
                font-size: 14px;
                line-height: 1.4;
                position: relative;
                box-shadow: 0 1px 1px rgba(0,0,0,0.05);
            }

            .chat-bubble-sent {
                background: #2563eb; /* Premium Indigo/Blue */
                color: #fff;
                border-bottom-right-radius: 2px;
            }

            .chat-bubble-received {
                background: #fff;
                color: #1f2937;
                border-bottom-left-radius: 2px;
                border: 1px solid #e5e7eb;
            }

            .single-chat-item:hover .message-actions {
                opacity: 1 !important;
                visibility: visible !important;
            }

            .message-actions {
                transition: all 0.2s ease;
                opacity: 0;
                visibility: hidden;
                z-index: 20;
            }

            /* Dark Mode Overrides */
            html.app-skin-dark .whatsapp-chat-app-container {
                background: #111827;
            }

            html.app-skin-dark .chat-bubble-received {
                background: #1f2937;
                color: #f3f4f6;
                border-color: #374151;
            }

            html.app-skin-dark .bg-white {
                background: #111827 !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function loadConversation(id) {
                window.location.href = `/dashboard/whatsapp-chat/${id}`;
            }

            // Conversation search
            document.getElementById('conversationSearch')?.addEventListener('input', function(e) {
                const q = e.target.value.toLowerCase();
                document.querySelectorAll('.single-item').forEach(item => {
                    const name = item.querySelector('.fw-bold').textContent.toLowerCase();
                    item.style.display = name.includes(q) ? 'flex' : 'none';
                });
            });

            // Edit/Delete logic (Re-implemented for safety)
            document.addEventListener('click', function(e) {
                const editBtn = e.target.closest('.edit-btn');
                if (editBtn) {
                    const id = editBtn.dataset.messageId;
                    const content = editBtn.dataset.content;
                    document.getElementById('editMessageId').value = id;
                    document.getElementById('editMessageContent').value = content;
                    new bootstrap.Modal(document.getElementById('editMessageModal')).show();
                }

                const deleteBtn = e.target.closest('.delete-btn');
                if (deleteBtn) {
                    const id = deleteBtn.dataset.messageId;
                    Swal.fire({
                        title: 'Delete message?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33'
                    }).then(res => {
                        if (res.isConfirmed) {
                            fetch(`/dashboard/whatsapp-chat/messages/${id}/delete`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            }).then(() => location.reload());
                        }
                    });
                }
            });

            document.getElementById('saveEditBtn')?.addEventListener('click', function() {
                const id = document.getElementById('editMessageId').value;
                const content = document.getElementById('editMessageContent').value;
                fetch(`/dashboard/whatsapp-chat/messages/${id}/edit`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify({
                        content
                    })
                }).then(() => location.reload());
            });
        </script>
    @endpush
