<div class="relative w-8 h-8">
    <div class="container-notifications">
        <div class="notifications">
            <input type="checkbox" name="one" id="view-notifications" class="hide-input">
            <label for="view-notifications">
                <span class="iconify notification_icon" data-icon="mingcute:bell-ringing-fill">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <g fill="none" fill-rule="evenodd">
                            <path d="m12.594 23.258l-.012.002l-.071.035l-.02.004l-.014-.004l-.071-.036q-.016-.004-.024.006l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.016-.018m.264-.113l-.014.002l-.184.093l-.01.01l-.003.011l.018.43l.005.012l.008.008l.201.092q.019.005.029-.008l.004-.014l-.034-.614q-.005-.019-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.003-.011l.018-.43l-.003-.012l-.01-.01z" />
                            <path fill="currentColor" d="M6.972 3.777a1 1 0 1 0-1.258-1.554a10 10 0 0 0-2.602 3.19a1 1 0 1 0 1.776.919a8 8 0 0 1 2.084-2.555m11.314-1.554a1 1 0 1 0-1.258 1.554a8 8 0 0 1 2.09 2.568a1 1 0 1 0 1.778-.916a10 10 0 0 0-2.61-3.206M5 10a7 7 0 0 1 14 0v3.764l1.822 3.644A1.1 1.1 0 0 1 19.838 19H4.162a1.1 1.1 0 0 1-.984-1.592L5 13.764zm4 10h6a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2" />
                        </g>
                    </svg>
                </span>
                @if ($unreadCount > 0)
                    <span class="bubble">
                        <span>{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                    </span>
                @endif
            </label>
            <div class="notifications-display">
                <div class="relative overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-slate-200">
                    <div class="flex items-center justify-between gap-3 px-4 py-2">
                        <span class="label flex gap-1 rounded bg-white px-2 pb-1 pt-1.5">
                            Notificaciones
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-primary font-semibold text-white">
                                {{ $unreadCount }}
                            </span>
                        </span>
                        <button type="button" class="btn-primary" wire:click="markAllAsRead" @disabled($unreadCount === 0)>
                            Marcar como leidos
                        </button>
                    </div>

                    <div class="max-h-96 w-full overflow-y-auto bg-white">
                        @forelse ($notifications as $notification)
                            @php
                                $data = $notification->data ?? [];
                                $isUnread = is_null($notification->read_at);
                            @endphp
                            <a
                                href="{{ $data['url'] ?? '#' }}"
                                wire:click="markAsRead('{{ $notification->id }}')"
                                class="block border-t border-slate-100 px-4 py-3 transition hover:bg-slate-50 {{ $isUnread ? 'bg-primary/5' : 'bg-white' }}">
                                <div class="flex items-start gap-3">
                                    <span class="mt-1 h-2.5 w-2.5 flex-none rounded-full {{ $isUnread ? 'bg-primary' : 'bg-slate-300' }}"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-slate-800">{{ $data['title'] ?? 'Notificacion' }}</p>
                                        <p class="mt-1 text-sm text-slate-600">{{ $data['body'] ?? '' }}</p>
                                        <p class="mt-1 text-xs text-slate-400">{{ $notification->created_at?->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="w-full max-w-md p-4">
                                <p><strong>Sin notificaciones</strong><br>
                                    No hay notificaciones en este momento
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
