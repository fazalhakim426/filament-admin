To fulfill the requirements for the Supplier and Reseller (Customer) Apps, the following API endpoints can be created for both:

### Supplier App Endpoints:

#### 1. **User Authentication**
- `POST /api/login` — Login with email and password (Admin-provided credentials).
- `POST /api/password/forgot` — Request password recovery (via email/OTP).
- `POST /api/password/reset` — Reset password using OTP or token.
- `POST /api/logout` — Logout and invalidate session.

#### 2. **Product Management**
- `POST /api/products` — Add a new product (requires category, name, description, price, and stock quantity).
- `PUT /api/products/{product_id}` — Edit product details (except for deactivation).
- `GET /api/products` — List products (with filters for active, category, price).
- `GET /api/products/{product_id}` — View product details.
- `PATCH /api/products/{product_id}/deactivate` — Deactivate a product (no delete option).
- `GET /api/products/{product_id}/performance` — View product performance (sales and stock status).

#### 3. **Shop Management**
- `GET /api/shop` — View shop details (admin managed).
  
#### 4. **Order Management**
- `GET /api/orders` — View incoming customer orders.
- `GET /api/orders/{order_id}` — View details of a specific order.
- `PATCH /api/orders/{order_id}/confirm` — Confirm an order.
- `PATCH /api/orders/{order_id}/reject` — Reject an order.
- `PATCH /api/orders/{order_id}/dispatch` — Update order status to dispatched.
- `GET /api/orders/{order_id}/airway-bill` — Generate and download airway bill for confirmed orders.

#### 5. **Payment Tracking**
- `GET /api/payments` — View payment details for completed orders.
- `GET /api/payments/weekly-invoice` — Access weekly invoices (completed orders, earnings, and deductions).
- `GET /api/payments/status` — Track payment status for weekly payouts.
  
#### 6. **Notifications**
- `GET /api/notifications` — List all notifications (orders, payments, announcements).
- `POST /api/notifications/send` — Send a new notification (e.g., for critical updates).

#### 7. **Analytics Dashboard**
- `GET /api/analytics/products-sales` — View product-wise sales and trends.
- `GET /api/analytics/revenue-history` — View revenue and payment history insights.
  
#### 8. **Customer Communication**
- `POST /api/orders/{order_id}/whatsapp-update` — Send WhatsApp updates to customers regarding orders.

---

### Reseller (Customer) App Endpoints:

#### 1. **User Authentication**
- `POST /api/login` — Login with OTP verification via email or phone.
- `POST /api/password/forgot` — Request password recovery (via OTP/email link).
- `POST /api/password/reset` — Reset password using OTP or token.
- `POST /api/logout` — Logout and invalidate session.

#### 2. **Dashboard**
- `GET /api/dashboard` — View total earnings, commissions, and sales history.
- `GET /api/dashboard/leaderboard` — Access performance metrics and leaderboard rankings.

#### 3. **Product Catalog**
- `GET /api/products` — View and filter product listings (by category, price, availability).
- `GET /api/products/{product_id}` — View product details.
- `POST /api/products/{product_id}/share` — Share product details via referral code/link.

#### 4. **Order Management**
- `POST /api/orders` — Place a new customer order.
- `GET /api/orders/{order_id}` — Track order status (confirmed, shipped, delivered).

#### 5. **Payment Tracking**
- `GET /api/payments` — Monitor commission payments.
- `GET /api/payments/history` — Access commission payment history and breakdowns.

#### 6. **Referral System**
- `GET /api/referrals` — Generate unique referral codes or links.
- `GET /api/referrals/earnings` — Track referrals and associated earnings.

#### 7. **Notifications**
- `GET /api/notifications` — List notifications for orders, commissions, platform news.

#### 8. **Customer Support**
- `POST /api/support/whatsapp` — Direct support via WhatsApp for queries.
- `POST /api/support/email` — Direct support via email for issues.

---

### Notes:
- For payment systems, integrate external payment gateway APIs for tracking transactions.
- For notifications, implement real-time push notifications via services like Firebase or Laravel broadcasting.
- Use `softDeletes` for soft deletion of records where appropriate, like in the `products`, `orders`, and `suppliers` tables.
- Authentication and session management will need JWT or OAuth for securing the APIs.

These endpoints aim to fulfill the core requirements for both the supplier and reseller apps while ensuring the functionality you outlined is covered.