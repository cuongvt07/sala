# Hướng Dẫn Sử Dụng Admin Panel

Dự án đã được khởi tạo thành công với stack **Laravel 11 + Livewire + Filament**.

## 1. Khởi chạy dự án
Mở terminal và chạy lệnh sau để bật server development:

```bash
php artisan serve
```

Truy cập đường dẫn: [http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin)

## 2. Tài khoản quản trị (Admin)
Đã tạo sẵn tài khoản admin để bạn đăng nhập:

*   **Email:** `admin@sala.com`
*   **Password:** `password`

## 3. Các chức năng đã triển khai
Hệ thống Admin đã có sẵn các menu quản lý:
*   **Areas:** Quản lý danh sách tòa nhà/khu vực.
*   **Rooms:** Quản lý phòng (gắn với Area), giá, trạng thái.
*   **Customers:** Quản lý thông tin khách hàng.
*   **Bookings:** Tạo và quản lý lịch đặt phòng (Check-in/Check-out).
*   **Booking Calendar:** Xem lịch biểu trực quan dạng Grid (Timline). Truy cập qua menu bên trái hoặc [tại đây](http://127.0.0.1:8000/admin/booking-calendar).

## 4. Cấu trúc Source Code
*   **Models:** `app/Models` (Area, Room, Customer, Booking).
*   **Filament Resources:** `app/Filament/Resources` (Chứa logic Admin).
*   **Database:** `database/database.sqlite` (SQLite database file).

## 5. Tiếp theo
Bạn có thể bắt đầu nhập liệu thử các Khu vực và Phòng.
Bước tiếp theo chúng ta sẽ phát triển **Booking Grid View** (Lịch biểu trực quan) như yêu cầu.
