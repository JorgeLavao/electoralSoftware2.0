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
                <span class="bubble">
                    <span>99</span>
                </span>
            </label>
            <div class="notifications-display">
                <div class="relative bg-gray-200 overflow-hidden rounded-xl shadow-lg">
                    <div class="flex items-center justify-between w-full px-4 py-2">
                        <span class="label flex pt-1.5 pb-1 px-2 gap-1 bg-white rounded">
                            Notificaciones
                            <span class="inline-flex items-center justify-center w-5 h-5 font-semibold text-white bg-primary rounded-full">
                                0
                            </span>
                        </span>
                        <form action="https://app.miclientela.com/notificaciones/change_status_all" method="POST" class="inline-flex">
                            <input type="hidden" name="_token" value="MqFaDCAQYD3AtpkiRVsHsTd9v8kHfZnq2JzWvfOQ">
                            <input type="hidden" name="_method" value="put">
                            <button type="submit" class="btn-primary" aria-label="Close">
                                Marcar como leídos
                            </button>
                        </form>
                    </div>
                    <div class="w-full bg-white">
                        <div class="w-full max-w-md p-4">
                            <p><strong>Sin notificaciones</strong>
                                No hay notificaciones en este momento
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
