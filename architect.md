
Project Kanto_stock {
  database_type: 'MySQL'
  Note: 'PWA de gestion commerciale et de stocks multi-magasins'
}

// ==========================================
// 1. ENTREPRISE ET UTILISATEURS
// ==========================================

Table companies {
  id bigint [pk, increment]
  name varchar(180) [not null]
  legal_name varchar(200)
  tax_number varchar(80)
  address text
  phone varchar(40)
  email varchar(150)
  logo_path varchar(255)
  default_currency_id bigint
  is_active boolean [not null, default: true]
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp
}

Table users {
  id bigint [pk, increment]
  company_id bigint [not null]
  name varchar(180) [not null]
  email varchar(180) [not null]
  phone varchar(40)
  password varchar(255) [not null]
  is_active boolean [not null, default: true]
  email_verified_at timestamp
  remember_token varchar(100)
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp

  indexes {
    (company_id, email) [unique]
  }
}

Table stocks {
  id bigint [pk, increment]
  company_id bigint [not null]
  code varchar(40) [not null]
  name varchar(150) [not null]
  description text
  address text
  is_active boolean [not null, default: true]
  created_by bigint
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp

  indexes {
    (company_id, code) [unique]
  }
}

Table stock_user {
  id bigint [pk, increment]
  stock_id bigint [not null]
  user_id bigint [not null]
  assigned_by bigint
  can_view boolean [not null, default: true]
  can_manage_stock boolean [not null, default: false]
  can_sell boolean [not null, default: false]
  is_default boolean [not null, default: false]
  assigned_at timestamp

  indexes {
    (stock_id, user_id) [unique]
  }
}

// ==========================================
// 2. ROLES ET PERMISSIONS - SPATIE
// ==========================================

Table roles {
  id bigint [pk, increment]
  name varchar(125) [not null]
  guard_name varchar(125) [not null, default: 'web']
  company_id bigint
  created_at timestamp
  updated_at timestamp

  indexes {
    (company_id, name, guard_name) [unique]
  }
}

Table permissions {
  id bigint [pk, increment]
  name varchar(125) [not null]
  guard_name varchar(125) [not null, default: 'web']
  created_at timestamp
  updated_at timestamp

  indexes {
    (name, guard_name) [unique]
  }
}

Table role_has_permissions {
  permission_id bigint [not null]
  role_id bigint [not null]

  indexes {
    (permission_id, role_id) [pk]
  }
}

Table model_has_roles {
  role_id bigint [not null]
  model_type varchar(255) [not null]
  model_id bigint [not null]

  indexes {
    (role_id, model_id, model_type) [pk]
  }
}

Table model_has_permissions {
  permission_id bigint [not null]
  model_type varchar(255) [not null]
  model_id bigint [not null]

  indexes {
    (permission_id, model_id, model_type) [pk]
  }
}

// ==========================================
// 3. DEVISES ET PARAMETRES FISCAUX
// ==========================================

Table currencies {
  id bigint [pk, increment]
  code varchar(3) [not null, unique, note: 'BIF, USD ou EUR']
  name varchar(80) [not null]
  symbol varchar(10)
  decimal_places int [not null, default: 2]
  is_active boolean [not null, default: true]
}

Table company_settings {
  id bigint [pk, increment]
  company_id bigint [not null]
  setting_key varchar(100) [not null]
  setting_value text
  created_at timestamp
  updated_at timestamp

  indexes {
    (company_id, setting_key) [unique]
  }
}

Table number_sequences {
  id bigint [pk, increment]
  company_id bigint [not null]
  stock_id bigint
  document_type varchar(40) [not null]
  prefix varchar(20) [not null]
  next_number bigint [not null, default: 1]
  padding int [not null, default: 6]
  updated_at timestamp

  indexes {
    (company_id, stock_id, document_type) [unique]
  }
}

// ==========================================
// 4. CATALOGUE PRODUITS
// ==========================================

Table categories {
  id bigint [pk, increment]
  company_id bigint [not null]
  parent_id bigint
  name varchar(150) [not null]
  description text
  is_active boolean [not null, default: true]
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp
}

Table product_labels {
  id bigint [pk, increment]
  company_id bigint [not null]
  name varchar(180) [not null]
  description text
  default_tax_rate decimal(7,4) [not null, default: 0]
  tax_included_in_price boolean [not null, default: false]
  is_tax_exempt boolean [not null, default: false]
  is_active boolean [not null, default: true]
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp

  Note: 'Le taux doit être validé et copié sur les lignes de vente'
}

Table units {
  id bigint [pk, increment]
  company_id bigint [not null]
  name varchar(80) [not null]
  abbreviation varchar(20) [not null]
  allows_decimal boolean [not null, default: false]
  is_active boolean [not null, default: true]
}

Table products {
  id bigint [pk, increment]
  company_id bigint [not null]
  category_id bigint [not null]
  product_label_id bigint [not null]
  unit_id bigint [not null]
  sku varchar(100) [not null]
  barcode varchar(100)
  name varchar(180) [not null]
  description text
  purchase_price decimal(19,4) [not null, default: 0]
  selling_price decimal(19,4) [not null, default: 0]
  currency_id bigint [not null]
  min_stock_level decimal(19,4) [not null, default: 0]
  image_path varchar(255)
  is_active boolean [not null, default: true]
  created_by bigint
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp

  indexes {
    (company_id, sku) [unique]
    (company_id, barcode)
    (company_id, name)
  }
}

// ==========================================
// 5. SOLDES ET MOUVEMENTS DE STOCK
// ==========================================

Table stock_items {
  id bigint [pk, increment]
  stock_id bigint [not null]
  product_id bigint [not null]
  quantity_on_hand decimal(19,4) [not null, default: 0]
  quantity_reserved decimal(19,4) [not null, default: 0]
  updated_at timestamp

  indexes {
    (stock_id, product_id) [unique]
  }

  Note: 'Solde courant. Disponible = en stock - réservé'
}

Table stock_movements {
  id bigint [pk, increment]
  company_id bigint [not null]
  reference varchar(60) [not null]
  movement_type varchar(30) [not null, note: 'initial_entry, entry, exit, transfer_in, transfer_out, adjustment, sale, sale_return']
  status varchar(20) [not null, default: 'draft', note: 'draft, validated, cancelled']
  source_stock_id bigint
  destination_stock_id bigint
  reason text
  document_date datetime [not null]
  created_by bigint [not null]
  validated_by bigint
  validated_at timestamp
  created_at timestamp
  updated_at timestamp

  indexes {
    (company_id, reference) [unique]
    (company_id, document_date)
  }
}

Table stock_movement_lines {
  id bigint [pk, increment]
  stock_movement_id bigint [not null]
  product_id bigint [not null]
  quantity decimal(19,4) [not null]
  unit_cost decimal(19,4)
  source_quantity_before decimal(19,4)
  source_quantity_after decimal(19,4)
  destination_quantity_before decimal(19,4)
  destination_quantity_after decimal(19,4)
  note text
  created_at timestamp
}

// ==========================================
// 6. CLIENTS
// ==========================================

Table customers {
  id bigint [pk, increment]
  company_id bigint [not null]
  code varchar(50)
  name varchar(180) [not null]
  phone varchar(40)
  email varchar(180)
  address text
  tax_number varchar(80)
  credit_limit decimal(19,4)
  currency_id bigint
  notes text
  is_active boolean [not null, default: true]
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp

  indexes {
    (company_id, code) [unique]
    (company_id, name)
    (company_id, phone)
  }
}

// ==========================================
// 7. VENTES ET LIGNES DE VENTE
// ==========================================

Table sales {
  id bigint [pk, increment]
  company_id bigint [not null]
  stock_id bigint [not null]
  customer_id bigint
  invoice_number varchar(60)
  status varchar(25) [not null, default: 'draft', note: 'draft, validated, cancelled']
  sale_date datetime [not null]
  currency_id bigint [not null]
  exchange_rate decimal(19,8) [not null, default: 1]
  subtotal decimal(19,4) [not null, default: 0]
  discount_amount decimal(19,4) [not null, default: 0]
  tax_amount decimal(19,4) [not null, default: 0]
  total_amount decimal(19,4) [not null, default: 0]
  amount_paid decimal(19,4) [not null, default: 0]
  balance_due decimal(19,4) [not null, default: 0]
  notes text
  created_by bigint [not null]
  validated_by bigint
  validated_at timestamp
  cancelled_by bigint
  cancelled_at timestamp
  cancellation_reason text
  created_at timestamp
  updated_at timestamp

  indexes {
    (company_id, invoice_number) [unique]
    (company_id, sale_date)
    (stock_id, status, sale_date)
  }
}

Table sale_items {
  id bigint [pk, increment]
  sale_id bigint [not null]
  product_id bigint [not null]
  product_name_snapshot varchar(180) [not null]
  product_sku_snapshot varchar(100)
  label_name_snapshot varchar(180) [not null]
  quantity decimal(19,4) [not null]
  unit_price decimal(19,4) [not null]
  discount_amount decimal(19,4) [not null, default: 0]
  tax_rate_snapshot decimal(7,4) [not null, default: 0]
  tax_amount decimal(19,4) [not null, default: 0]
  line_subtotal decimal(19,4) [not null]
  line_total decimal(19,4) [not null]
  created_at timestamp

  Note: 'Snapshots pour préserver le contenu historique de la facture'
}

// ==========================================
// 8. FACTURES
// ==========================================

Table invoices {
  id bigint [pk, increment]
  company_id bigint [not null]
  sale_id bigint [not null]
  invoice_number varchar(60) [not null]
  invoice_date datetime [not null]
  status varchar(20) [not null, default: 'issued', note: 'issued, cancelled']
  customer_name_snapshot varchar(180)
  customer_address_snapshot text
  customer_tax_number_snapshot varchar(80)
  subtotal decimal(19,4) [not null]
  discount_amount decimal(19,4) [not null]
  tax_amount decimal(19,4) [not null]
  total_amount decimal(19,4) [not null]
  currency_id bigint [not null]
  issued_by bigint [not null]
  created_at timestamp
  updated_at timestamp

  indexes {
    (company_id, invoice_number) [unique]
    (sale_id) [unique]
  }
}

// ==========================================
// 9. MODES ET ENREGISTREMENT DES PAIEMENTS
// ==========================================

Table payment_methods {
  id bigint [pk, increment]
  code varchar(30) [not null, unique, note: 'cash, bank, credit, other']
  name varchar(80) [not null]
  is_active boolean [not null, default: true]
}

Table payments {
  id bigint [pk, increment]
  company_id bigint [not null]
  sale_id bigint [not null]
  payment_method_id bigint [not null]
  currency_id bigint [not null]
  amount decimal(19,4) [not null]
  exchange_rate decimal(19,8) [not null, default: 1]
  reference varchar(120)
  payment_date datetime [not null]
  status varchar(20) [not null, default: 'confirmed', note: 'pending, confirmed, cancelled']
  cash_session_id bigint
  received_by bigint [not null]
  notes text
  created_at timestamp
  updated_at timestamp
}

// ==========================================
// 10. CAISSES ET MOUVEMENTS FINANCIERS
// ==========================================

Table cash_registers {
  id bigint [pk, increment]
  company_id bigint [not null]
  stock_id bigint [not null]
  name varchar(100) [not null]
  currency_id bigint [not null]
  is_active boolean [not null, default: true]
  created_at timestamp
  updated_at timestamp
}

Table cash_sessions {
  id bigint [pk, increment]
  cash_register_id bigint [not null]
  user_id bigint [not null]
  opening_amount decimal(19,4) [not null, default: 0]
  expected_amount decimal(19,4)
  counted_amount decimal(19,4)
  difference_amount decimal(19,4)
  status varchar(20) [not null, default: 'open', note: 'open, closed']
  opened_at datetime [not null]
  closed_at datetime
  closing_notes text
}

Table cash_movements {
  id bigint [pk, increment]
  cash_session_id bigint [not null]
  movement_type varchar(20) [not null, note: 'in, out, adjustment']
  amount decimal(19,4) [not null]
  reference_type varchar(50)
  reference_id bigint
  description text
  created_by bigint [not null]
  created_at timestamp
}

// ==========================================
// 11. DEPENSES
// ==========================================

Table expense_categories {
  id bigint [pk, increment]
  company_id bigint [not null]
  name varchar(120) [not null]
  is_active boolean [not null, default: true]
}

Table expenses {
  id bigint [pk, increment]
  company_id bigint [not null]
  stock_id bigint
  expense_category_id bigint [not null]
  cash_session_id bigint
  reference varchar(60) [not null]
  description text [not null]
  amount decimal(19,4) [not null]
  currency_id bigint [not null]
  payment_method_id bigint [not null]
  expense_date datetime [not null]
  status varchar(20) [not null, default: 'validated', note: 'draft, validated, cancelled']
  receipt_path varchar(255)
  created_by bigint [not null]
  validated_by bigint
  created_at timestamp
  updated_at timestamp

  indexes {
    (company_id, reference) [unique]
    (company_id, expense_date)
  }
}

// ==========================================
// 12. JOURNAL D'AUDIT
// ==========================================

Table audit_logs {
  id bigint [pk, increment]
  company_id bigint [not null]
  user_id bigint
  action varchar(100) [not null]
  auditable_type varchar(255) [not null]
  auditable_id bigint
  old_values json
  new_values json
  ip_address varchar(45)
  user_agent text
  created_at timestamp
}

// ==========================================
// RELATIONS
// ==========================================

Ref: companies.default_currency_id > currencies.id
Ref: users.company_id > companies.id
Ref: stocks.company_id > companies.id
Ref: stocks.created_by > users.id
Ref: stock_user.stock_id > stocks.id
Ref: stock_user.user_id > users.id
Ref: stock_user.assigned_by > users.id

Ref: roles.company_id > companies.id
Ref: role_has_permissions.role_id > roles.id
Ref: role_has_permissions.permission_id > permissions.id
Ref: model_has_roles.role_id > roles.id
Ref: model_has_permissions.permission_id > permissions.id

Ref: company_settings.company_id > companies.id
Ref: number_sequences.company_id > companies.id
Ref: number_sequences.stock_id > stocks.id

Ref: categories.company_id > companies.id
Ref: categories.parent_id > categories.id
Ref: product_labels.company_id > companies.id
Ref: units.company_id > companies.id
Ref: products.company_id > companies.id
Ref: products.category_id > categories.id
Ref: products.product_label_id > product_labels.id
Ref: products.unit_id > units.id
Ref: products.currency_id > currencies.id
Ref: products.created_by > users.id

Ref: stock_items.stock_id > stocks.id
Ref: stock_items.product_id > products.id
Ref: stock_movements.company_id > companies.id
Ref: stock_movements.source_stock_id > stocks.id
Ref: stock_movements.destination_stock_id > stocks.id
Ref: stock_movements.created_by > users.id
Ref: stock_movements.validated_by > users.id
Ref: stock_movement_lines.stock_movement_id > stock_movements.id
Ref: stock_movement_lines.product_id > products.id

Ref: customers.company_id > companies.id
Ref: customers.currency_id > currencies.id

Ref: sales.company_id > companies.id
Ref: sales.stock_id > stocks.id
Ref: sales.customer_id > customers.id
Ref: sales.currency_id > currencies.id
Ref: sales.created_by > users.id
Ref: sales.validated_by > users.id
Ref: sales.cancelled_by > users.id
Ref: sale_items.sale_id > sales.id
Ref: sale_items.product_id > products.id

Ref: invoices.company_id > companies.id
Ref: invoices.sale_id > sales.id
Ref: invoices.currency_id > currencies.id
Ref: invoices.issued_by > users.id

Ref: payments.company_id > companies.id
Ref: payments.sale_id > sales.id
Ref: payments.payment_method_id > payment_methods.id
Ref: payments.currency_id > currencies.id
Ref: payments.cash_session_id > cash_sessions.id
Ref: payments.received_by > users.id

Ref: cash_registers.company_id > companies.id
Ref: cash_registers.stock_id > stocks.id
Ref: cash_registers.currency_id > currencies.id
Ref: cash_sessions.cash_register_id > cash_registers.id
Ref: cash_sessions.user_id > users.id
Ref: cash_movements.cash_session_id > cash_sessions.id
Ref: cash_movements.created_by > users.id

Ref: expense_categories.company_id > companies.id
Ref: expenses.company_id > companies.id
Ref: expenses.stock_id > stocks.id
Ref: expenses.expense_category_id > expense_categories.id
Ref: expenses.cash_session_id > cash_sessions.id
Ref: expenses.currency_id > currencies.id
Ref: expenses.payment_method_id > payment_methods.id
Ref: expenses.created_by > users.id
Ref: expenses.validated_by > users.id

Ref: audit_logs.company_id > companies.id
Ref: audit_logs.user_id > users.id

