# TÀI LIỆU YÊU CẦU DỰ ÁN: SALA APARTMENT MANAGEMENT SYSTEM

## 1. TỔNG QUAN DỰ ÁN
Xây dựng hệ thống quản lý tổng thể cho chuỗi căn hộ cho thuê SALA APARTMENT. Hệ thống tập trung vào việc quản lý tập trung đa điểm (nhiều tòa nhà/khu vực), quản lý vòng đời khách thuê, và tối ưu hóa lịch đặt phòng.

## 2. STACK CÔNG NGHỆ ĐỀ XUẤT (LARAVEL + LIVEWIRE)
Dựa trên yêu cầu của bạn, hệ thống sẽ được xây dựng trên nền tảng PHP hiện đại:

*   **Backend Framework:** Laravel 11.x
*   **Frontend Approach:** Laravel Livewire 3 (Server-side rendering, reactive components) + Alpine.js (Client-side interactions).
*   **Styling:** TailwindCSS.
*   **Database:** MySQL hoặc PostgreSQL.
*   **Admin Panel / UI Components:**
    *   Có thể cân nhắc sử dụng **FilamentPHP** (dựa trên TALL stack: Tailwind, Alpine, Laravel, Livewire) để tăng tốc độ phát triển 80% phần quản trị (Admin), sau đó custom phần Booking Grid.

## 3. CHI TIẾT CHỨC NĂNG

### 3.1. Quản Lý Khu Vực & Phòng (Property Management)
*   **Cấu trúc đa tầng:**
    *   **Khu vực (Areas/Buildings):** Quản lý các tòa nhà riêng biệt.
    *   **Phòng (Units/Apartments):** Các căn hộ con trong tòa nhà.
*   **Quản lý Phòng:**
    *   Thông tin: Mã phòng, Tên, Loại (Studio, 1BR, 2BR), Diện tích, Giá (Ngày/Tháng).
    *   Tiện ích: Danh sách checkbox thiết bị, nội thất.
    *   Media: Upload nhiều hình ảnh (Sử dụng Laravel Spatie Media Library).
    *   Trạng thái: Available (Trống), Occupied (Đang thuê), Maintenance (Bảo trì), Reserved (Đã cọc).
*   **Tính năng đặc biệt:**
    *   **Clone Room:** Chức năng sao chép cấu trúc phòng để tạo nhanh dữ liệu.
    *   **Global Search:** Tìm kiếm phòng trên toàn bộ hệ thống hoặc filter theo từng khu.

### 3.2. Quản Lý Khách Hàng & Hồ Sơ (CRM)
*   **Thông tin định danh:** Họ tên, CMT/CCCD/Passport, Ngày sinh, Quốc tịch.
*   **Quản lý VISA:** Theo dõi loại Visa và ngày hết hạn (cho khách nước ngoài).
*   **Lưu trữ tài liệu:** Upload ảnh chụp giấy tờ tùy thân, ảnh chân dung, ảnh Visa.
*   **Lịch sử khách hàng:** Tự động tổng hợp lịch sử các lần thuê, tổng tiền đã chi, ratings/notes xấu (blacklist).

### 3.3. Hệ Thống Đặt Phòng & Booking Grid (Core Feature)
Đây là chức năng quan trọng nhất, cần giao diện trực quan.
*   **Cấu trúc Booking:**
    *   Liên kết Khách hàng <-> Phòng.
    *   Thời gian: Check-in / Check-out (Dự kiến & Thực tế).
    *   Tài chính: Giá thuê, Tiền cọc, Payment Status (Chưa thanh toán/Một phần/Hoàn tất).
    *   Trạng thái: Pending -> Confirmed -> Checked-In -> Checked-Out -> Cancelled.
*   **Giao Diện Booking Grid (Schedule View):**
    *   Sử dụng thư viện JS wrapper cho Livewire (ví dụ: FullCalendar hoặc tự build Grid bằng CSS Grid + Alpine.js để tối ưu performance).
    *   **Trục tung:** Danh sách phòng (Group theo Khu).
    *   **Trục hoành:** Dòng thời gian (Ngày).
    *   **Thao tác:**
        *   Drag & Drop để dời ngày booking.
        *   Resize block để tăng/giảm số ngày.
        *   Click empty slot để tạo booking mới.
        *   Hover để xem Quick Info.

### 3.4. Cấu Hình & Hệ Thống (Settings)
*   **Email Automation:**
    *   Gửi mail xác nhận booking, nhắc hạn thanh toán, nhắc check-in/out.
*   **Phân quyền (RBAC):**
    *  Cơ bản admin và nhân viên
*   **Cấu hình động:** Giá tiền tệ, email hóa đơn, email báo cáo, email nhắc nhở.

### 3.5. Ứng Dụng Di Động & Responsive
*   Toàn bộ giao diện Web Admin sẽ được thiết kế **Mobile-First** với TailwindCSS.
*   Livewire hỗ trợ SPA (Single Page Application) navigation với `wire:navigate`, giúp trải nghiệm trên mobile mượt mà như app native.
*   Tính năng mobile focus:
    *   Upload ảnh trực tiếp từ camera điện thoại khi làm thủ tục check-in.
    *   Gọi điện nhanh cho khách qua link `tel:`.

## 4. UI/UX DESIGN CONCEPT
*   **Phong cách:** Minimalist, Modern Dashboard.
*   **Điều hướng thông minh:**
    *   **Global Sidebar:** Menu chính.
    *   **Context Switcher:** Dropdown ở Header để chuyển nhanh giữa các Chi nhánh/Khu vực. Khi chọn Khu A, toàn bộ số liệu Dashboard sẽ filter theo Khu A.
    *   **Quick Actions:** Nút "Create Booking" luôn hiển thị (Floating Action Button trên mobile).

## 5. CẤU TRÚC DỮ LIỆU SƠ BỘ (DATABASE SCHEMA)
*   `users`: Quản trị viên, nhân viên.
*   `areas`: Các tòa nhà/chi nhánh.
*   `rooms`: Danh sách phòng (liên kết `area_id`).
*   `customers`: Hồ sơ khách hàng.
*   `bookings`: Đơn đặt phòng (liên kết `room_id`, `customer_id`).
*   `booking_items`: (Optional) Chi tiết các dịch vụ thêm.
*   `invoices`: Hóa đơn thanh toán.
*   `settings`: Cấu hình hệ thống.

## 6. LỘ TRÌNH PHÁT TRIỂN (ROADMAP)
*   **Phase 1 (MVP):**
    *   Setup Laravel + Livewire + Database.
    *   CRUD Khu vực, Phòng, Khách hàng.
    *   Tạo Booking cơ bản (Form nhập liệu).
    *   Booking Grid View (Read-only hoặc Basic Interaction).
*   **Phase 2 (Automation & Enhancement):**
    *   Booking Grid Advanced (Drag & Drop, Resize).
    *   Email Automation.
    *   Thống kê Dashboard.
*   **Phase 3 (Mobile & Optimization):**
    *   Tối ưu UI mobile.
    *   PWA (Progressive Web App) để cài đặt lên điện thoại.

---
**Ghi chú:** Bạn có thể chỉnh sửa file này trực tiếp để cập nhật/thêm bớt các yêu cầu chi tiết trước khi chúng ta bắt đầu code.
