@php
    $isAdmin = fn($message) => in_array($message->sender_type, ['admin', 'superadmin']) &&
        $message->sender_id === auth()->id();
    $isOtherAdmin = fn($message) => in_array($message->sender_type, ['admin', 'superadmin']) &&
        $message->sender_id !== auth()->id();
@endphp

<!-- [ Content Area ] start -->
<div class="d-flex flex-column h-100">
    <!-- [ Content Area Header ] start -->
    <div class="content-area-header sticky-top">
        <div class="gap-4 page-header-left hstack">
            <a href="javascript:void(0);" class="app-sidebar-open-trigger">
                <i class="feather-align-left fs-20"></i>
            </a>
            <a href="javascript:void(0);" class="gap-3 d-flex align-items-center justify-content-center"
                data-bs-toggle="offcanvas" data-bs-target="#userProfileDetails">
                <div class="avatar-image">
                    <div class="text-white avatar-text bg-primary">
                        {{ strtoupper(substr($conversation->profile_name, 0, 1)) }}
                    </div>
                </div>
                <div class="d-none d-sm-block">
                    <div class="fw-bold d-flex align-items-center">{{ $conversation->profile_name }}</div>
                    <div class="mt-1 d-flex align-items-center">
                        <span class="opacity-75 wd-7 ht-7 rounded-circle me-2 bg-success"></span>
                        <span class="fs-9 text-uppercase fw-bold text-success">Active Now</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="page-header-right ms-auto">
            <div class="gap-2 d-flex align-items-center justify-content-center">
                <a href="javascript:void(0)" class="d-flex">
                    <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover"
                        title="Voice Call">
                        <i class="feather-phone-call"></i>
                    </div>
                </a>
                <a href="javascript:void(0)" class="d-flex">
                    <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover"
                        title="Video Call">
                        <i class="feather-video"></i>
                    </div>
                </a>
                <a href="javascript:void(0)" class="d-flex d-none d-sm-block">
                    <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover"
                        title="Add to Favorite">
                        <i class="feather-star"></i>
                    </div>
                </a>
                <a href="javascript:void(0)" class="ac-info-sidebar-open-trigger" data-bs-toggle="offcanvas"
                    data-bs-target="#userProfileDetails">
                    <div class="avatar-text avatar-md" data-bs-toggle="tooltip" data-bs-trigger="hover"
                        title="Profile Info">
                        <i class="feather-info"></i>
                    </div>
                </a>
                <div class="dropdown">
                    <a href="javascript:void(0);" class="avatar-text avatar-md" data-bs-toggle="dropdown"
                        data-bs-offset="0,22">
                        <i class="feather-more-vertical"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-plus me-3"></i>
                            <span>Join Group</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-user-plus me-3"></i>
                            <span>Invite People</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-star me-3"></i>
                            <span>Add to Favorite</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-bell-off me-3"></i>
                            <span>Mute Conversion</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-phone-call me-3"></i>
                            <span>Group Audio Call</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-video me-3"></i>
                            <span>Group Video Call</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-slash me-3"></i>
                            <span>Block Conversion</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item" id="closeBtn">
                            <i class="feather-trash-2 me-3"></i>
                            <span>Delete Conversion</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Content Area Header ] end -->

    <!-- [ Content Area Body ] start -->
    <div class="content-area-body" id="messagesContainer">
        @forelse($messages as $message)
            @php
                $admin = $isAdmin($message);
                $otherAdmin = $isOtherAdmin($message);
            @endphp

            <div class="single-chat-item d-flex flex-column {{ $admin ? 'align-items-end' : 'align-items-start' }} mb-4">
                <!-- Message Header (Name + Time) -->
                <div class="d-flex align-items-center mb-1 {{ $admin ? 'flex-row-reverse' : '' }} gap-2">
                    <span class="fw-bold fs-12 text-dark">
                        @if($admin) You @elseif($otherAdmin) {{ $message->sender->name ?? 'Admin' }} @else {{ $conversation->profile_name }} @endif
                    </span>
                    <span class="opacity-75 fs-10 text-muted">{{ $message->created_at->format('H:i') }}</span>
                    @if($message->edited_at)
                        <span class="italic fs-9 text-muted">(edited)</span>
                    @endif
                </div>

                <!-- Message Content Wrapper -->
                <div class="d-flex {{ $admin ? 'flex-row-reverse' : '' }} align-items-start gap-3 chat-bubble-container">
                    <!-- Avatar -->
                    <div class="flex-shrink-0 avatar-image">
                        @if ($admin)
                            <div class="shadow-sm avatar-text bg-soft-success text-success rounded-circle wd-35 ht-35 d-flex align-items-center justify-content-center fw-bold fs-9">U</div>
                        @elseif ($otherAdmin)
                            <div class="shadow-sm avatar-text bg-soft-warning text-warning rounded-circle wd-35 ht-35 d-flex align-items-center justify-content-center fw-bold fs-9">{{ strtoupper(substr($message->sender->name ?? 'A', 0, 1)) }}</div>
                        @else
                            <div class="text-white shadow-sm avatar-text bg-primary rounded-circle wd-35 ht-35 d-flex align-items-center justify-content-center fw-bold fs-9">{{ strtoupper(substr($conversation->profile_name, 0, 1)) }}</div>
                        @endif
                    </div>

                    <!-- Bubble -->
                    <div class="position-relative">
                        <div class="chat-bubble {{ $admin ? 'chat-bubble-sent' : 'chat-bubble-received' }}">
                            @if($otherAdmin)
                                <div class="mb-1 fs-10 fw-black text-uppercase text-warning">{{ $message->sender->name }}</div>
                            @endif
                            <div class="chat-content-text" style="word-break: break-word;">
                                {!! nl2br(e($message->content)) !!}
                            </div>
                        </div>

                        <!-- Reactions Display -->
                        @if ($message->reactions && count($message->reactions) > 0)
                            <div class="message-reactions-pill position-absolute bottom-0 {{ $admin ? 'start-0' : 'end-0' }} translate-middle-y mb-n2 bg-white rounded-pill px-2 py-1 shadow-xs border fs-10">
                                @foreach ($message->reactions as $emoji => $userIds)
                                    <span title="{{ count($userIds) }} orang">{{ $emoji }}</span>
                                @endforeach
                            </div>
                        @endif

                        <!-- Hover Actions -->
                        <div class="message-actions position-absolute top-50 {{ $admin ? 'start-0 translate-middle-x' : 'end-0 translate-middle-x' }} pe-3">
                            <div class="overflow-hidden bg-white border shadow-sm btn-group btn-group-sm rounded-pill">
                                <button class="border-0 btn btn-icon btn-sm reaction-btn" data-message-id="{{ $message->id }}" title="React">
                                    <i class="feather-smile fs-12"></i>
                                </button>
                                @if ($admin)
                                    <button class="border-0 btn btn-icon btn-sm edit-btn" data-message-id="{{ $message->id }}" data-content="{{ e($message->content) }}" title="Edit">
                                        <i class="feather-edit fs-12"></i>
                                    </button>
                                    <button class="border-0 btn btn-icon btn-sm delete-btn text-danger" data-message-id="{{ $message->id }}" title="Delete">
                                        <i class="feather-trash-2 fs-12"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="opacity-50 h-100 d-flex align-items-center justify-content-center flex-column">
                <div class="mb-4 avatar-text avatar-xl rounded-circle">
                    <i class="feather-message-square fs-1"></i>
                </div>
                <h5 class="fw-bold">No messages yet</h5>
                <p class="fs-12">Start the conversation below.</p>
            </div>
        @endforelse
    </div>
    <!-- [ Content Area Body ] end -->

    <!-- [ Message Editor ] start -->
    <div class="p-3 bg-white shadow-sm border-top sticky-bottom">
        @if (!$isWorkingHours)
            <div class="px-3 py-2 mb-3 border-0 alert alert-soft-warning d-flex align-items-center justify-content-between rounded-pill" style="font-size: 11px;">
                <div>
                    <i class="feather-alert-circle me-2"></i>
                    <strong>Di Luar Jam Kerja!</strong> Admin akan segera membalas pesan Anda.
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" style="font-size: 8px;"></button>
            </div>
        @endif

        <div class="gap-3 d-flex align-items-center">
            <!-- Action Buttons -->
            <div class="gap-1 d-flex align-items-center">
                <div class="dropdown">
                    <button class="btn btn-icon btn-light-brand rounded-circle wd-45 ht-45" data-bs-toggle="dropdown" title="Pick Template">
                        <i class="feather-hash"></i>
                    </button>
                    <ul class="border-0 shadow-lg dropdown-menu wd-300">
                        @forelse($templates->groupBy('category') as $category => $categoryTemplates)
                            <li class="dropdown-header text-uppercase fs-9 fw-black ls-1">{{ $category }}</li>
                            @foreach ($categoryTemplates as $template)
                                <li>
                                    <a href="javascript:void(0)" class="dropdown-item template-btn" data-template-id="{{ $template->id }}">
                                        <i class="feather-file-text me-3 text-primary"></i>{{ $template->name }}
                                    </a>
                                </li>
                            @endforeach
                        @empty
                            <li><span class="dropdown-item text-muted">No templates available</span></li>
                        @endforelse
                    </ul>
                </div>

                <div class="dropdown">
                    <button class="btn btn-icon btn-light-brand rounded-circle wd-45 ht-45" data-bs-toggle="dropdown" title="Attachments">
                        <i class="feather-paperclip"></i>
                    </button>
                    <ul class="border-0 shadow-lg dropdown-menu">
                        <li><label class="dropdown-item c-pointer"><i class="feather-image me-3 text-success"></i>Image<input type="file" id="imageUpload" accept="image/*" class="d-none"></label></li>
                        <li><label class="dropdown-item c-pointer"><i class="feather-mic me-3 text-warning"></i>Audio<input type="file" id="audioUpload" accept="audio/*" class="d-none"></label></li>
                        <li><label class="dropdown-item c-pointer"><i class="feather-video me-3 text-danger"></i>Video<input type="file" id="videoUpload" accept="video/*" class="d-none"></label></li>
                        <li><label class="dropdown-item c-pointer"><i class="feather-file me-3 text-info"></i>File<input type="file" id="documentUpload" class="d-none"></label></li>
                    </ul>
                </div>
            </div>

            <!-- Input Area -->
            <div class="flex-grow-1 position-relative">
                <textarea id="messageInput" class="px-4 py-3 border-0 form-control bg-soft-primary rounded-4"
                          rows="1" placeholder="Tulis pesan..." style="resize: none; overflow-y: hidden; min-height: 50px; line-height: 24px; box-shadow: none;"></textarea>
            </div>

            <!-- Send Button -->
            <button type="button" id="sendBtn" class="border-0 btn btn-primary rounded-circle wd-50 ht-50 d-flex align-items-center justify-content-center shadow-primary">
                <i class="feather-send"></i>
            </button>
        </div>
    </div>
    <!-- [ Message Editor ] end -->
</div>
<!-- [ Content Area ] end -->

<!-- [ User Profile Details ] start -->
<div class="offcanvas offcanvas-end" id="userProfileDetails" aria-hidden="true" tabindex="-1">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title">Profile Info</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="p-0 offcanvas-body">
        <div class="p-4 text-center position-relative">
            <div
                class="mx-auto mt-3 text-white border border-5 avatar-text bg-primary wd-100 ht-100 rounded-circle fs-1 d-flex align-items-center justify-content-center">
                {{ strtoupper(substr($conversation->profile_name, 0, 1)) }}
            </div>
            <h2 class="mt-4 mb-0 text-dark fs-18 fw-bold">{{ $conversation->profile_name }}</h2>
            <span class="mb-3 fs-12 text-muted d-block">{{ $conversation->phone_number }}</span>
            <div class="mt-3 d-flex justify-content-center">
                <a href="tel:{{ $conversation->phone_number }}"
                    class="avatar-text avatar-md bg-soft-primary text-primary me-2"><i class="feather-phone"></i></a>
                <a href="javascript:void(0)" class="avatar-text avatar-md bg-soft-success text-success"><i
                        class="feather-external-link"></i></a>
            </div>
        </div>
        <a class="px-4 py-2 bg-gray-100 fs-12 fw-bold d-block border-top border-bottom" data-bs-toggle="collapse"
            href="#PersonalInfo">Information</a>
        <div class="p-4 fs-13 collapse show" id="PersonalInfo">
            <div class="mb-4 d-flex align-items-start">
                <div class="me-3"><i class="feather-user text-primary"></i></div>
                <div>
                    <div class="mb-1 fs-9 text-uppercase fw-bold text-muted">Status</div>
                    <span class="badge {{ $conversation->status === 'active' ? 'bg-success' : 'bg-gray-400' }}">
                        {{ strtoupper($conversation->status) }}
                    </span>
                </div>
            </div>
            @if ($conversation->student)
                <div class="mb-4 d-flex align-items-start">
                    <div class="me-3"><i class="feather-book-open text-primary"></i></div>
                    <div>
                        <div class="mb-1 fs-9 text-uppercase fw-bold text-muted">Student</div>
                        <div class="fw-bold">{{ $conversation->student->name }}</div>
                        <div class="text-muted fs-11">NISN: {{ $conversation->student->nisn }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
<!-- [ User Profile Details ] end -->

<style>
    main.apps-chat .chat-content-text {
        color: #111827;
    }

    main.apps-chat #messageInput {
        color: inherit;
        background-color: transparent;
    }

    main.apps-chat #messageInput::placeholder {
        color: #6c757d;
        opacity: 1;
    }

    html.app-skin-dark main.apps-chat .chat-content-text {
        color: #ffffff;
    }

    html.app-skin-dark main.apps-chat #messageInput {
        color: #ffffff;
    }

    html.app-skin-dark main.apps-chat #messageInput::placeholder {
        color: rgba(255, 255, 255, 0.65);
    }

    .message-reactions-pill {
        z-index: 5;
    }
</style>

<script>
    (function() {
        const convId = '{{ $conversation->id }}';
        const container = document.getElementById('messagesContainer');
        const input = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        const csrfToken = '{{ csrf_token() }}';

        if (input) {
            input.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        }

        function scrollBottom() {
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }
        scrollBottom();

        if (window.Echo) {
            window.Echo.leave(`whatsapp-conversation.${convId}`);
            window.Echo.private(`whatsapp-conversation.${convId}`)
                .listen('.message-sent', (e) => {
                    if (e.sender_id == '{{ auth()->id() }}') return;
                    if (typeof loadConversation === 'function') {
                        fetch(`/dashboard/whatsapp-chat/${convId}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(res => res.text())
                        .then(html => {
                            document.getElementById('chatPaneContainer').innerHTML = html;
                        });
                    }
                });
        }

        async function sendMessage() {
            const content = input.value.trim();
            if (!content) return;

            input.value = '';
            input.style.height = '50px';

            try {
                const response = await fetch(`/dashboard/whatsapp-chat/${convId}/send`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ content })
                });

                const result = await response.json();
                if (result.success) {
                    const paneResponse = await fetch(`/dashboard/whatsapp-chat/${convId}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const html = await paneResponse.text();
                    document.getElementById('chatPaneContainer').innerHTML = html;
                } else if (response.status === 403 && result.work_hours_restricted) {
                    Swal.fire({
                        title: 'Di Luar Jam Kerja',
                        text: 'Admin sedang tidak bertugas. Kirim paksa?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Kirim',
                        cancelButtonText: 'Batal'
                    }).then(async (choice) => {
                        if (choice.isConfirmed) {
                            const retry = await fetch(`/dashboard/whatsapp-chat/${convId}/send`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ content, force_send_outside_hours: true })
                            });
                            const retryResult = await retry.json();
                            if (retryResult.success) {
                                const paneResponse = await fetch(`/dashboard/whatsapp-chat/${convId}`, {
                                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                });
                                const html = await paneResponse.text();
                                document.getElementById('chatPaneContainer').innerHTML = html;
                            }
                        } else {
                            input.value = content;
                        }
                    });
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Gagal mengirim pesan', 'error');
                input.value = content;
            }
        }

        sendBtn?.addEventListener('click', sendMessage);
        input?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        document.querySelectorAll('.template-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const id = this.dataset.templateId;
                try {
                    const res = await fetch(`/dashboard/whatsapp-chat/${convId}/send-template`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ template_id: id })
                    });
                    const data = await res.json();
                    if (data.success) {
                        const paneRes = await fetch(`/dashboard/whatsapp-chat/${convId}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const html = await paneRes.text();
                        document.getElementById('chatPaneContainer').innerHTML = html;
                    }
                } catch (e) {
                    Swal.fire('Error', 'Gagal mengirim template', 'error');
                }
            });
        });

        const handleFile = (el, type) => {
            const file = el.files[0];
            if (!file) return;

            const fd = new FormData();
            fd.append('media_file', file);
            fd.append('message_type', type);

            Swal.fire({
                title: 'Mengunggah...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(`/dashboard/whatsapp-chat/${convId}/send`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    fetch(`/dashboard/whatsapp-chat/${convId}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('chatPaneContainer').innerHTML = html;
                    });
                }
            });
            el.value = '';
        };

        document.getElementById('imageUpload')?.addEventListener('change', function() { handleFile(this, 'image'); });
        document.getElementById('audioUpload')?.addEventListener('change', function() { handleFile(this, 'audio'); });
        document.getElementById('videoUpload')?.addEventListener('change', function() { handleFile(this, 'video'); });
        document.getElementById('documentUpload')?.addEventListener('change', function() { handleFile(this, 'document'); });

        window.addEventListener('beforeunload', () => {
            if (window.Echo && convId) {
                window.Echo.leave(`whatsapp-conversation.${convId}`);
            }
            if (pollingInterval) {
                clearInterval(pollingInterval);
            }
        });
    })();
</script>
