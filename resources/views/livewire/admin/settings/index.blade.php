<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Cài đặt hệ thống</h1>
            <p class="text-sm text-gray-500">Quản lý cấu hình SMTP, email và các thiết lập chung.</p>
        </div>
        <button wire:click="save" class="relative group overflow-hidden px-6 py-2.5 bg-gradient-to-tr from-blue-600 to-indigo-700 text-white rounded-xl shadow-lg hover:shadow-blue-500/30 transition-all duration-300">
            <span class="relative z-10 flex items-center gap-2">
                <x-icon name="heroicon-o-check" class="w-5 h-5" />
                Lưu thay đổi
            </span>
            <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
        </button>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-xl flex items-center gap-3 animate-in fade-in slide-in-from-top-4">
            <x-icon name="heroicon-o-check-circle" class="w-5 h-5" />
            {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left column: Navigation Tabs -->
        <div class="lg:col-span-3 space-y-2">
            <div class="bg-white/80 backdrop-blur-xl border border-white/20 p-2 rounded-2xl shadow-sm sticky top-24">
                <button class="w-full flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-50 text-blue-700 font-medium">
                    <x-icon name="heroicon-o-envelope" class="w-5 h-5" />
                    Cấu hình Email
                </button>
                <button class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                    <x-icon name="heroicon-o-computer-desktop" class="w-5 h-5" />
                    Cài đặt chung
                </button>
            </div>
        </div>

        <!-- Right column: Settings Content -->
        <div class="lg:col-span-9 space-y-6">
            <!-- SMTP Card -->
            <div class="bg-white/80 backdrop-blur-xl border border-white/20 rounded-3xl shadow-sm overflow-hidden animate-in fade-in duration-500">
                <div class="bg-gradient-to-r from-slate-50 to-white px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                            <x-icon name="heroicon-o-server-stack" class="w-5 h-5" />
                        </div>
                        <h2 class="font-bold text-gray-900">Cấu hình SMTP</h2>
                    </div>
                </div>
                
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5 focus-within:text-blue-600 transition-colors">
                        <label class="text-xs font-bold uppercase tracking-wider text-gray-400">SMTP Host</label>
                        <input type="text" wire:model="settings.mail_host" class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all" placeholder="smtp.gmail.com">
                    </div>

                    <div class="space-y-1.5 focus-within:text-blue-600 transition-colors">
                        <label class="text-xs font-bold uppercase tracking-wider text-gray-400">SMTP Port</label>
                        <input type="number" wire:model="settings.mail_port" class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all" placeholder="587">
                    </div>

                    <div class="space-y-1.5 focus-within:text-blue-600 transition-colors">
                        <label class="text-xs font-bold uppercase tracking-wider text-gray-400">Email người gửi</label>
                        <input type="email" wire:model="settings.mail_username" class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all" placeholder="example@gmail.com">
                    </div>

                    <div class="space-y-1.5 focus-within:text-blue-600 transition-colors">
                        <label class="text-xs font-bold uppercase tracking-wider text-gray-400">Mật khẩu ứng dụng</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" wire:model="settings.mail_password" class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                            <button @click="show = !show" type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600">
                                <x-icon x-show="!show" name="heroicon-o-eye" class="w-5 h-5" />
                                <x-icon x-show="show" name="heroicon-o-eye-slash" class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <div class="space-y-1.5 focus-within:text-blue-600 transition-colors">
                        <label class="text-xs font-bold uppercase tracking-wider text-gray-400">Mã hóa (Encryption)</label>
                        <select wire:model="settings.mail_encryption" class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all appearance-none">
                            <option value="tls">TLS</option>
                            <option value="ssl">SSL</option>
                            <option value="">None</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Email Structure Card -->
            <div class="bg-white/80 backdrop-blur-xl border border-white/20 rounded-3xl shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-6 duration-700">
                <div class="bg-gradient-to-r from-slate-50 to-white px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-100 text-indigo-600 rounded-lg">
                            <x-icon name="heroicon-o-chat-bubble-bottom-center-text" class="w-5 h-5" />
                        </div>
                        <h2 class="font-bold text-gray-900">Cấu trúc thư gửi đi</h2>
                    </div>
                </div>
                
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-400">Tên hiển thị người gửi</label>
                            <input type="text" wire:model="settings.mail_from_name" class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-400">Tiền tố tiêu đề (Subject Prefix)</label>
                            <input type="text" wire:model="settings.mail_subject_prefix" class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-gray-400">Chữ ký chân trang (Footer)</label>
                        <textarea wire:model="settings.mail_footer_text" rows="2" class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all resize-none"></textarea>
                    </div>

                    <!-- Email Preview (Glassmorphism) -->
                    <div class="mt-4 p-6 bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl relative overflow-hidden group">
                        <!-- Decorative glow -->
                        <div class="absolute -top-12 -right-12 w-32 h-32 bg-blue-500/20 blur-3xl rounded-full transition-all duration-500 group-hover:scale-150"></div>
                        <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-indigo-500/20 blur-3xl rounded-full transition-all duration-500 group-hover:scale-150"></div>

                        <div class="relative bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-6 shadow-2xl">
                            <div class="flex items-center gap-4 mb-6 pb-6 border-b border-white/5">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 text-white flex items-center justify-center font-black text-xl shadow-lg ring-2 ring-white/20">
                                    {{ substr($settings['site_name'] ?? 'S', 0, 1) }}
                                </div>
                                <div class="flex-1">
                                    <div class="text-white font-bold flex items-center gap-2">
                                        {{ $settings['mail_from_name'] ?: 'SALA System' }}
                                        <span class="px-2 py-0.5 rounded-full bg-blue-500/20 text-[10px] text-blue-300 border border-blue-500/30 uppercase tracking-tighter">Verified</span>
                                    </div>
                                    <div class="text-white/40 text-xs">customer@example.com</div>
                                </div>
                                <div class="text-white/30 text-[10px] bg-white/5 px-2 py-1 rounded-md">Today, 10:24 AM</div>
                            </div>

                            <div class="space-y-4">
                                <div class="text-white/90 font-bold text-lg">
                                    {{ $settings['mail_subject_prefix'] }} XÁC NHẬN ĐẶT PHÒNG THÀNH CÔNG #SL9921
                                </div>
                                <div class="bg-white/5 border border-white/5 rounded-xl p-4 text-white/70 text-sm leading-relaxed italic">
                                    Chào bạn, đây là bản xem trước nội dung email được gửi từ hệ thống của bạn. Giao diện này mô phỏng cách khách hàng sẽ nhìn thấy thông tin từ thương hiệu của bạn.
                                </div>
                                <div class="pt-6 mt-6 border-t border-white/5 text-center text-white/30 text-[10px] tracking-widest uppercase">
                                    {{ $settings['mail_footer_text'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
