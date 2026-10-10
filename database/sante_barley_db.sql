-- PostgreSQL / Supabase schema. Execute through psql or the Supabase SQL editor.
BEGIN;

CREATE TABLE IF NOT EXISTS categories (
    id   BIGSERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS users (
    id             BIGSERIAL PRIMARY KEY,
    full_name      VARCHAR(120) NOT NULL,
    username       VARCHAR(60) UNIQUE NOT NULL,
    email          VARCHAR(254) UNIQUE NOT NULL,
    mobile         VARCHAR(30) NOT NULL DEFAULT '',
    password       TEXT NOT NULL,
    address        TEXT NOT NULL DEFAULT '',
    province       VARCHAR(100) NOT NULL DEFAULT '',
    city           VARCHAR(100) NOT NULL DEFAULT '',
    barangay       VARCHAR(100) NOT NULL DEFAULT '',
    postal_code    VARCHAR(20) NOT NULL DEFAULT '',
    profile_image  TEXT NOT NULL DEFAULT '',
    status         VARCHAR(20) NOT NULL DEFAULT 'Active' CHECK (status IN ('Active', 'Inactive')),
    created_at     TIMESTAMPTZ DEFAULT NOW(),
    updated_at     TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS admins (
    id      BIGSERIAL PRIMARY KEY,
    user_id BIGINT UNIQUE NOT NULL REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS products (
    id                   BIGSERIAL PRIMARY KEY,
    category_id          BIGINT REFERENCES categories(id),
    product_name         VARCHAR(180) NOT NULL,
    slug                 VARCHAR(200) UNIQUE NOT NULL,
    description          TEXT NOT NULL DEFAULT '',
    price                NUMERIC(12, 2) NOT NULL CHECK (price >= 0),
    discount             NUMERIC(5, 2) NOT NULL DEFAULT 0 CHECK (discount BETWEEN 0 AND 90),
    stock                INTEGER NOT NULL DEFAULT 0 CHECK (stock >= 0),
    low_stock_threshold  INTEGER NOT NULL DEFAULT 10,
    sku                  VARCHAR(50) UNIQUE NOT NULL,
    image                TEXT NOT NULL,
    status               VARCHAR(30) DEFAULT 'Active' CHECK (status IN ('Active', 'Inactive', 'Out of Stock')),
    size                 VARCHAR(100) DEFAULT '',
    ingredients          TEXT DEFAULT 'Please refer to the current product label for the complete ingredient list and allergen information.',
    instructions         TEXT DEFAULT 'Always follow the product label and official instructions. Consult a qualified healthcare professional if you have questions.',
    created_at           TIMESTAMPTZ DEFAULT NOW(),
    updated_at           TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS orders (
    id               BIGSERIAL PRIMARY KEY,
    user_id          BIGINT NOT NULL REFERENCES users(id),
    order_number     VARCHAR(50) UNIQUE NOT NULL,
    subtotal         NUMERIC(12, 2) NOT NULL,
    shipping_fee     NUMERIC(12, 2) NOT NULL,
    total_amount     NUMERIC(12, 2) NOT NULL,
    payment_method   VARCHAR(40) NOT NULL CHECK (payment_method IN ('Cash on Delivery', 'GCash', 'Maya', 'GrabPay', 'ShopeePay', 'Credit / Debit Card', 'Bank Transfer')),
    payment_status   VARCHAR(20) NOT NULL DEFAULT 'Unpaid' CHECK (payment_status IN ('Unpaid', 'Paid', 'Refunded')),
    order_status     VARCHAR(20) NOT NULL DEFAULT 'Pending' CHECK (order_status IN ('Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled')),
    shipping_address TEXT NOT NULL,
    notes            TEXT DEFAULT '',
    created_at       TIMESTAMPTZ DEFAULT NOW(),
    updated_at       TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS order_items (
    id           BIGSERIAL PRIMARY KEY,
    order_id     BIGINT NOT NULL REFERENCES orders(id),
    product_id   BIGINT REFERENCES products(id),
    product_name TEXT NOT NULL,
    quantity     INTEGER NOT NULL CHECK (quantity > 0),
    price        NUMERIC(12, 2) NOT NULL,
    subtotal     NUMERIC(12, 2) NOT NULL
);

CREATE TABLE IF NOT EXISTS cart (
    user_id    BIGINT REFERENCES users(id),
    product_id BIGINT REFERENCES products(id),
    quantity   INTEGER CHECK (quantity > 0),
    PRIMARY KEY (user_id, product_id)
);

CREATE TABLE IF NOT EXISTS wishlist (
    user_id    BIGINT REFERENCES users(id),
    product_id BIGINT REFERENCES products(id),
    PRIMARY KEY (user_id, product_id)
);

CREATE TABLE IF NOT EXISTS payments (
    id         BIGSERIAL PRIMARY KEY,
    order_id   BIGINT UNIQUE REFERENCES orders(id),
    method     VARCHAR(30) NOT NULL,
    amount     NUMERIC(12, 2) NOT NULL,
    status     VARCHAR(20) DEFAULT 'Unpaid',
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS reviews (
    id         BIGSERIAL PRIMARY KEY,
    user_id    BIGINT REFERENCES users(id),
    product_id BIGINT REFERENCES products(id),
    rating     INTEGER CHECK (rating BETWEEN 1 AND 5),
    content    TEXT NOT NULL,
    status     VARCHAR(20) DEFAULT 'Pending' CHECK (status IN ('Pending', 'Approved', 'Rejected')),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE (user_id, product_id)
);

CREATE TABLE IF NOT EXISTS messages (
    id         BIGSERIAL PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    email      VARCHAR(254) NOT NULL,
    subject    VARCHAR(180) NOT NULL,
    content    TEXT NOT NULL,
    status     VARCHAR(20) DEFAULT 'Unread',
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS notifications (
    id         BIGSERIAL PRIMARY KEY,
    user_id    BIGINT REFERENCES users(id),
    content    TEXT NOT NULL,
    is_read    BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS settings (
    key   VARCHAR(80) PRIMARY KEY,
    value TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS promotions (
    id          BIGSERIAL PRIMARY KEY,
    title       VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    active      BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS login_attempts (
    identity     TEXT PRIMARY KEY,
    attempts     INTEGER DEFAULT 0,
    window_start TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS orders_user_idx       ON orders(user_id);
CREATE INDEX IF NOT EXISTS order_items_order_idx ON order_items(order_id);

INSERT INTO categories(name) VALUES
    ('Santé Barley'),
    ('Capsules'),
    ('Specialty Drinks'),
    ('Beauty'),
    ('Essentials'),
    ('Wellness')
ON CONFLICT DO NOTHING;

INSERT INTO settings(key, value) VALUES
    ('shipping_fee', '120'),
    ('free_shipping_threshold', '2500'),
    ('store_name', 'Santé Barley Philippines')
ON CONFLICT DO NOTHING;

-- No anonymous access: PHP uses a private database connection.
-- Enable RLS for Supabase Data API.
DO $$
DECLARE
    t TEXT;
BEGIN
    FOREACH t IN ARRAY ARRAY[
        'categories', 'users', 'admins', 'products', 'orders', 'order_items',
        'cart', 'wishlist', 'payments', 'reviews', 'messages', 'notifications',
        'settings', 'promotions', 'login_attempts'
    ]
    LOOP
        EXECUTE format('ALTER TABLE %I ENABLE ROW LEVEL SECURITY', t);
    END LOOP;
END $$;

COMMIT;