<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Initial extends AbstractMigration
{
    public bool $autoId = false;

    public function up(): void
    {

        $this->table('fcs_action_logs')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('type', 'string', [
                'default' => '',
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('customer_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('object_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('object_type', 'string', [
                'default' => '',
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('text', 'text', [
                'default' => null,
                'limit' => 16777215,
                'null' => false,
            ])
            ->addColumn('date', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('fcs_address')
            ->addColumn('id_address', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_address'])
            ->addColumn('id_customer', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('id_manufacturer', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('lastname', 'string', [
                'default' => '',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('firstname', 'string', [
                'default' => '',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('address1', 'string', [
                'default' => '',
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('address2', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('postcode', 'string', [
                'default' => null,
                'limit' => 12,
                'null' => true,
            ])
            ->addColumn('city', 'string', [
                'default' => '',
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('comment', 'text', [
                'default' => null,
                'limit' => 16777215,
                'null' => true,
            ])
            ->addColumn('phone', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('phone_mobile', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('email', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('date_add', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('date_upd', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'id_customer',
                ]
            )
            ->addIndex(
                [
                    'id_manufacturer',
                ]
            )
            ->create();

        $this->table('fcs_attribute')
            ->addColumn('id_attribute', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_attribute'])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('can_be_used_as_unit', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('active', 'integer', [
                'default' => '1',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('fcs_barcodes')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('product_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('product_attribute_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('barcode', 'string', [
                'default' => null,
                'limit' => 13,
                'null' => true,
            ])
            ->addIndex(
                [
                    'product_id',
                    'product_attribute_id',
                ]
            )
            ->addIndex(
                [
                    'barcode',
                ]
            )
            ->create();

        $this->table('fcs_blog_posts')
            ->addColumn('id_blog_post', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_blog_post'])
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 150,
                'null' => false,
            ])
            ->addColumn('short_description', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('content', 'text', [
                'default' => null,
                'limit' => 4294967295,
                'null' => false,
            ])
            ->addColumn('id_customer', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('id_manufacturer', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('is_private', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('active', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('show_on_start_page_until', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('fcs_cart_product_units')
            ->addColumn('id_cart_product', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('ordered_quantity_in_units', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 10,
                'scale' => 3,
                'signed' => false,
            ])
            ->create();

        $this->table('fcs_cart_products')
            ->addColumn('id_cart_product', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_cart_product'])
            ->addColumn('id_cart', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('id_product', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('id_product_attribute', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('amount', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('fcs_carts')
            ->addColumn('id_cart', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_cart'])
            ->addColumn('id_customer', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('cart_type', 'integer', [
                'default' => '1',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('status', 'tinyinteger', [
                'default' => '1',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('fcs_category')
            ->addColumn('id_category', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_category'])
            ->addColumn('id_parent', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => 16777215,
                'null' => false,
            ])
            ->addColumn('nleft', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('nright', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('active', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'id_parent',
                ]
            )
            ->addIndex(
                [
                    'nleft',
                    'nright',
                    'active',
                ]
            )
            ->addIndex(
                [
                    'nright',
                ]
            )
            ->addIndex(
                [
                    'active',
                    'nleft',
                ]
            )
            ->addIndex(
                [
                    'active',
                    'nright',
                ]
            )
            ->addIndex(
                [
                    'active',
                ]
            )
            ->create();

        $this->table('fcs_category_product')
            ->addColumn('id_category', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('id_product', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_category', 'id_product'])
            ->addIndex(
                [
                    'id_product',
                ]
            )
            ->addIndex(
                [
                    'id_category',
                ]
            )
            ->create();

        $this->table('fcs_configuration')
            ->addColumn('id_configuration', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_configuration'])
            ->addColumn('active', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => '',
                'limit' => 254,
                'null' => false,
            ])
            ->addColumn('text', 'text', [
                'default' => null,
                'limit' => 16777215,
                'null' => false,
            ])
            ->addColumn('value', 'text', [
                'default' => null,
                'limit' => 16777215,
                'null' => true,
            ])
            ->addColumn('type', 'string', [
                'default' => '',
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('position', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('locale', 'string', [
                'default' => null,
                'limit' => 5,
                'null' => true,
            ])
            ->addColumn('date_add', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('date_upd', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'name',
                ]
            )
            ->create();

        $this->table('fcs_cronjob_logs')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('cronjob_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('success', 'tinyinteger', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->create();

        $this->table('fcs_cronjobs')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('time_interval', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('day_of_month', 'tinyinteger', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('weekday', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('not_before_time', 'time', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('active', 'tinyinteger', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->create();

        $this->table('fcs_customer')
            ->addColumn('id_customer', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_customer'])
            ->addColumn('id_default_group', 'integer', [
                'default' => '1',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('is_company', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('firstname', 'string', [
                'default' => '',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('lastname', 'string', [
                'default' => '',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('email', 'string', [
                'default' => '',
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('passwd', 'char', [
                'default' => null,
                'limit' => 60,
                'null' => true,
            ])
            ->addColumn('tmp_new_passwd', 'char', [
                'default' => null,
                'limit' => 60,
                'null' => true,
            ])
            ->addColumn('activate_new_password_code', 'string', [
                'default' => null,
                'limit' => 12,
                'null' => true,
            ])
            ->addColumn('auto_login_hash', 'string', [
                'default' => null,
                'limit' => 40,
                'null' => true,
            ])
            ->addColumn('email_order_reminder_enabled', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('terms_of_use_accepted_date', 'date', [
                'default' => '1000-01-01',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('activate_email_code', 'string', [
                'default' => null,
                'limit' => 12,
                'null' => true,
            ])
            ->addColumn('active', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('date_add', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('date_upd', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('use_camera_for_barcode_scanning', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('user_id_registrierkasse', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('shopping_price', 'string', [
                'default' => 'SP',
                'limit' => 2,
                'null' => true,
            ])
            ->addColumn('check_credit_reminder_enabled', 'tinyinteger', [
                'default' => '1',
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('invoices_per_email_enabled', 'tinyinteger', [
                'default' => '1',
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('pickup_day_reminder_enabled', 'tinyinteger', [
                'default' => '1',
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('credit_upload_reminder_enabled', 'tinyinteger', [
                'default' => '1',
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('newsletter_enabled', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addIndex(
                [
                    'email',
                ]
            )
            ->addIndex(
                [
                    'email',
                    'passwd',
                ]
            )
            ->addIndex(
                [
                    'id_customer',
                    'passwd',
                ]
            )
            ->create();

        $this->table('fcs_deposits')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('id_product', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('id_product_attribute', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('deposit', 'float', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('fcs_feedbacks')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('customer_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('text', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('approved', 'datetime', [
                'default' => '1970-01-01 00:00:00',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('privacy_type', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('fcs_images')
            ->addColumn('id_image', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_image'])
            ->addColumn('id_product', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->create();

        $this->table('fcs_invoice_taxes')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('invoice_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('tax_rate', 'float', [
                'default' => '0.000000',
                'null' => false,
                'precision' => 20,
                'scale' => 6,
            ])
            ->addColumn('total_price_tax_excl', 'float', [
                'default' => '0.000000',
                'null' => false,
                'precision' => 20,
                'scale' => 6,
            ])
            ->addColumn('total_price_tax', 'float', [
                'default' => '0.000000',
                'null' => false,
                'precision' => 20,
                'scale' => 6,
            ])
            ->addColumn('total_price_tax_incl', 'float', [
                'default' => '0.000000',
                'null' => false,
                'precision' => 20,
                'scale' => 6,
            ])
            ->addIndex(
                [
                    'invoice_id',
                ]
            )
            ->create();

        $this->table('fcs_invoices')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('id_manufacturer', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('invoice_number', 'string', [
                'default' => '0',
                'limit' => 17,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('id_customer', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('paid_in_cash', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('filename', 'string', [
                'default' => '',
                'limit' => 512,
                'null' => false,
            ])
            ->addColumn('email_status', 'string', [
                'default' => null,
                'limit' => 30,
                'null' => true,
            ])
            ->addColumn('cancellation_invoice_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->create();

        $this->table('fcs_manufacturer')
            ->addColumn('id_manufacturer', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_manufacturer'])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => 4294967295,
                'null' => true,
            ])
            ->addColumn('short_description', 'text', [
                'default' => null,
                'limit' => 16777215,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('active', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('is_private', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('uid_number', 'string', [
                'default' => null,
                'limit' => 30,
                'null' => true,
            ])
            ->addColumn('additional_text_for_invoice', 'text', [
                'default' => null,
                'limit' => 16777215,
                'null' => true,
            ])
            ->addColumn('iban', 'string', [
                'default' => null,
                'limit' => 22,
                'null' => true,
            ])
            ->addColumn('bic', 'string', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('bank_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('firmenbuchnummer', 'string', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('firmengericht', 'string', [
                'default' => null,
                'limit' => 150,
                'null' => true,
            ])
            ->addColumn('aufsichtsbehoerde', 'string', [
                'default' => null,
                'limit' => 150,
                'null' => true,
            ])
            ->addColumn('kammer', 'string', [
                'default' => null,
                'limit' => 150,
                'null' => true,
            ])
            ->addColumn('homepage', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('id_customer', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('variable_member_fee', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('send_invoice', 'tinyinteger', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('send_order_list', 'tinyinteger', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('default_tax_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('default_tax_id_purchase_price', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('send_order_list_cc', 'string', [
                'default' => null,
                'limit' => 512,
                'null' => true,
            ])
            ->addColumn('send_instant_order_notification', 'tinyinteger', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('send_ordered_product_deleted_notification', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('send_ordered_product_price_changed_notification', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('send_ordered_product_amount_changed_notification', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('enabled_sync_domains', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('stock_management_enabled', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('send_product_sold_out_limit_reached_for_manufacturer', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('send_product_sold_out_limit_reached_for_contact_person', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('no_delivery_days', 'text', [
                'default' => null,
                'limit' => 16777215,
                'null' => true,
            ])
            ->addColumn('include_stock_products_in_order_lists', 'tinyinteger', [
                'default' => '1',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('send_delivery_notes', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addIndex(
                [
                    'stock_management_enabled',
                ]
            )
            ->create();

        $this->table('fcs_order_detail')
            ->addColumn('id_order_detail', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_order_detail'])
            ->addColumn('product_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('product_attribute_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('product_name', 'string', [
                'default' => '',
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('product_amount', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('total_price_tax_incl', 'decimal', [
                'default' => '0.000000',
                'null' => false,
                'precision' => 20,
                'scale' => 6,
            ])
            ->addColumn('total_price_tax_excl', 'decimal', [
                'default' => '0.000000',
                'null' => false,
                'precision' => 20,
                'scale' => 6,
            ])
            ->addColumn('tax_unit_amount', 'decimal', [
                'default' => '0.000000',
                'null' => false,
                'precision' => 16,
                'scale' => 6,
            ])
            ->addColumn('tax_total_amount', 'decimal', [
                'default' => '0.000000',
                'null' => false,
                'precision' => 16,
                'scale' => 6,
            ])
            ->addColumn('tax_rate', 'decimal', [
                'default' => '0.000',
                'null' => false,
                'precision' => 10,
                'scale' => 3,
            ])
            ->addColumn('deposit', 'decimal', [
                'default' => '0.00',
                'null' => false,
                'precision' => 10,
                'scale' => 2,
            ])
            ->addColumn('id_customer', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('id_invoice', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('id_cart_product', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('order_state', 'tinyinteger', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('pickup_day', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('shopping_price', 'string', [
                'default' => 'SP',
                'limit' => 2,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                [
                    'product_id',
                ]
            )
            ->addIndex(
                [
                    'product_attribute_id',
                ]
            )
            ->addIndex(
                [
                    'id_customer',
                ]
            )
            ->addIndex(
                [
                    'pickup_day',
                ]
            )
            ->addIndex(
                [
                    'created',
                ]
            )
            ->addIndex(
                [
                    'order_state',
                ]
            )
            ->addIndex(
                [
                    'product_name',
                ]
            )
            ->create();

        $this->table('fcs_order_detail_feedbacks')
            ->addColumn('id_order_detail', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_order_detail'])
            ->addColumn('text', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('customer_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->create();

        $this->table('fcs_order_detail_purchase_prices')
            ->addColumn('id_order_detail', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_order_detail'])
            ->addColumn('tax_rate', 'decimal', [
                'default' => '0.000',
                'null' => false,
                'precision' => 10,
                'scale' => 3,
            ])
            ->addColumn('total_price_tax_incl', 'decimal', [
                'default' => '0.000000',
                'null' => false,
                'precision' => 20,
                'scale' => 6,
            ])
            ->addColumn('total_price_tax_excl', 'decimal', [
                'default' => '0.000000',
                'null' => false,
                'precision' => 20,
                'scale' => 6,
            ])
            ->addColumn('tax_unit_amount', 'decimal', [
                'default' => '0.000000',
                'null' => false,
                'precision' => 16,
                'scale' => 6,
            ])
            ->addColumn('tax_total_amount', 'decimal', [
                'default' => '0.000000',
                'null' => false,
                'precision' => 16,
                'scale' => 6,
            ])
            ->create();

        $this->table('fcs_order_detail_units')
            ->addColumn('id_order_detail', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('product_quantity_in_units', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 10,
                'scale' => 3,
                'signed' => false,
            ])
            ->addColumn('price_incl_per_unit', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 10,
                'scale' => 2,
                'signed' => false,
            ])
            ->addColumn('purchase_price_incl_per_unit', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 10,
                'scale' => 2,
                'signed' => false,
            ])
            ->addColumn('quantity_in_units', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 10,
                'scale' => 3,
                'signed' => false,
            ])
            ->addColumn('unit_name', 'string', [
                'default' => '',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('unit_amount', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('mark_as_saved', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addIndex(
                [
                    'id_order_detail',
                ],
                ['unique' => true]
            )
            ->create();

        $this->table('fcs_pages')
            ->addColumn('id_page', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_page'])
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('content', 'text', [
                'default' => null,
                'limit' => 4294967295,
                'null' => false,
            ])
            ->addColumn('position', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('menu_type', 'string', [
                'default' => 'header',
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('active', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('extern_url', 'string', [
                'default' => '',
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('id_customer', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('is_private', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('full_width', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('id_parent', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('lft', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('rght', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('fcs_payments')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('id_customer', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('id_manufacturer', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('type', 'string', [
                'default' => 'product',
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('amount', 'decimal', [
                'default' => '0.00',
                'null' => false,
                'precision' => 10,
                'scale' => 2,
            ])
            ->addColumn('text', 'string', [
                'default' => '',
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('date_add', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('date_changed', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('date_transaction_add', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('transaction_text', 'text', [
                'default' => null,
                'limit' => 16777215,
                'null' => true,
            ])
            ->addColumn('invoice_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('status', 'tinyinteger', [
                'default' => '1',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('approval', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('approval_comment', 'text', [
                'default' => null,
                'limit' => 16777215,
                'null' => true,
            ])
            ->addColumn('changed_by', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('created_by', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->create();

        $this->table('fcs_pickup_days')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('customer_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('pickup_day', 'date', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('comment', 'text', [
                'default' => null,
                'limit' => 16777215,
                'null' => true,
            ])
            ->addColumn('products_picked_up', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addIndex(
                [
                    'customer_id',
                ]
            )
            ->addIndex(
                [
                    'pickup_day',
                ]
            )
            ->create();

        $this->table('fcs_product')
            ->addColumn('id_product', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_product'])
            ->addColumn('id_manufacturer', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('id_tax', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('id_storage_location', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('price', 'decimal', [
                'default' => '0.000000',
                'null' => false,
                'precision' => 20,
                'scale' => 6,
            ])
            ->addColumn('name', 'text', [
                'default' => null,
                'limit' => 16777215,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => 4294967295,
                'null' => true,
            ])
            ->addColumn('description_short', 'text', [
                'default' => null,
                'limit' => 16777215,
                'null' => true,
            ])
            ->addColumn('unity', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('is_declaration_ok', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('is_stock_product', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('active', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('delivery_rhythm_type', 'string', [
                'default' => 'week',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('delivery_rhythm_count', 'tinyinteger', [
                'default' => '1',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('delivery_rhythm_first_delivery_day', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('delivery_rhythm_order_possible_until', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('delivery_rhythm_send_order_list_weekday', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('delivery_rhythm_send_order_list_day', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'id_manufacturer',
                    'id_product',
                ]
            )
            ->addIndex(
                [
                    'id_manufacturer',
                ]
            )
            ->addIndex(
                [
                    'is_stock_product',
                ]
            )
            ->create();

        $this->table('fcs_product_attribute')
            ->addColumn('id_product_attribute', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_product_attribute'])
            ->addColumn('id_product', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('price', 'decimal', [
                'default' => '0.000000',
                'null' => false,
                'precision' => 20,
                'scale' => 6,
            ])
            ->addColumn('default_on', 'tinyinteger', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addIndex(
                [
                    'id_product',
                ]
            )
            ->addIndex(
                [
                    'id_product_attribute',
                    'id_product',
                ]
            )
            ->create();

        $this->table('fcs_product_attribute_combination')
            ->addColumn('id_attribute', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('id_product_attribute', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_attribute', 'id_product_attribute'])
            ->addIndex(
                [
                    'id_product_attribute',
                ]
            )
            ->create();

        $this->table('fcs_purchase_prices')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('product_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('product_attribute_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('tax_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('price', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 20,
                'scale' => 6,
            ])
            ->addIndex(
                [
                    'product_id',
                    'product_attribute_id',
                ]
            )
            ->create();

        $this->table('fcs_sliders')
            ->addColumn('id_slider', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_slider'])
            ->addColumn('image', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('link', 'string', [
                'default' => null,
                'limit' => 999,
                'null' => true,
            ])
            ->addColumn('is_private', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('position', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('active', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('fcs_stock_available')
            ->addColumn('id_stock_available', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_stock_available'])
            ->addColumn('id_product', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('id_product_attribute', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('quantity', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('quantity_limit', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('sold_out_limit', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('always_available', 'tinyinteger', [
                'default' => '1',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('default_quantity_after_sending_order_lists', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addIndex(
                [
                    'id_product',
                    'id_product_attribute',
                ],
                ['unique' => true]
            )
            ->addIndex(
                [
                    'id_product',
                ]
            )
            ->addIndex(
                [
                    'id_product_attribute',
                ]
            )
            ->create();

        $this->table('fcs_storage_locations')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('rank', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->create();

        $this->table('fcs_sync_domains')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('domain', 'string', [
                'default' => '',
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('active', 'tinyinteger', [
                'default' => '1',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('fcs_sync_products')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('sync_domain_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('local_product_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('remote_product_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('local_product_attribute_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('remote_product_attribute_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->create();

        $this->table('fcs_tax')
            ->addColumn('id_tax', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id_tax'])
            ->addColumn('rate', 'decimal', [
                'default' => '0.000',
                'null' => false,
                'precision' => 10,
                'scale' => 3,
            ])
            ->addColumn('active', 'tinyinteger', [
                'default' => '1',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('deleted', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->create();

        $this->table('fcs_units')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('id_product', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('id_product_attribute', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('price_incl_per_unit', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 10,
                'scale' => 2,
                'signed' => false,
            ])
            ->addColumn('purchase_price_incl_per_unit', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 10,
                'scale' => 2,
                'signed' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => '',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('amount', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('price_per_unit_enabled', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('quantity_in_units', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 10,
                'scale' => 3,
                'signed' => false,
            ])
            ->addIndex(
                [
                    'id_product',
                    'id_product_attribute',
                ],
                ['unique' => true]
            )
            ->create();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     * @return void
     */
    public function down()
    {
        $this->table('fcs_action_logs')->drop()->save();
        $this->table('fcs_address')->drop()->save();
        $this->table('fcs_attribute')->drop()->save();
        $this->table('fcs_barcodes')->drop()->save();
        $this->table('fcs_blog_posts')->drop()->save();
        $this->table('fcs_cart_product_units')->drop()->save();
        $this->table('fcs_cart_products')->drop()->save();
        $this->table('fcs_carts')->drop()->save();
        $this->table('fcs_category')->drop()->save();
        $this->table('fcs_category_product')->drop()->save();
        $this->table('fcs_configuration')->drop()->save();
        $this->table('fcs_cronjob_logs')->drop()->save();
        $this->table('fcs_cronjobs')->drop()->save();
        $this->table('fcs_customer')->drop()->save();
        $this->table('fcs_deposits')->drop()->save();
        $this->table('fcs_feedbacks')->drop()->save();
        $this->table('fcs_images')->drop()->save();
        $this->table('fcs_invoice_taxes')->drop()->save();
        $this->table('fcs_invoices')->drop()->save();
        $this->table('fcs_manufacturer')->drop()->save();
        $this->table('fcs_order_detail')->drop()->save();
        $this->table('fcs_order_detail_feedbacks')->drop()->save();
        $this->table('fcs_order_detail_purchase_prices')->drop()->save();
        $this->table('fcs_order_detail_units')->drop()->save();
        $this->table('fcs_pages')->drop()->save();
        $this->table('fcs_payments')->drop()->save();
        $this->table('fcs_pickup_days')->drop()->save();
        $this->table('fcs_product')->drop()->save();
        $this->table('fcs_product_attribute')->drop()->save();
        $this->table('fcs_product_attribute_combination')->drop()->save();
        $this->table('fcs_purchase_prices')->drop()->save();
        $this->table('fcs_sliders')->drop()->save();
        $this->table('fcs_stock_available')->drop()->save();
        $this->table('fcs_storage_locations')->drop()->save();
        $this->table('fcs_sync_domains')->drop()->save();
        $this->table('fcs_sync_products')->drop()->save();
        $this->table('fcs_tax')->drop()->save();
        $this->table('fcs_units')->drop()->save();
    }
}
