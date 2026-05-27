# ERD Documentation: Payment & WhatsApp Gateway

## 1. Payment System Flow Chart (Mermaid)

```mermaid
flowchart TD
    subgraph Student
        S[Student]
    end

    subgraph Payment_Module
        PT[PaymentTitle]
        P[Payment]
        C[Charge]
        SF[StudentFee]
        E[Events]
    end

    subgraph Midtrans
        MT[Midtrans Gateway]
        MT_API[Midtrans API]
    end

    subgraph Notification
        L[Listeners]
        M[Mail]
        N[Notifications]
    end

    S -->|creates| P
    PT -->|defines| P
    P -->|generates| C
    P -->|creates| SF
    C -->|connects| MT_API
    MT_API -->|callback| MT
    MT -->|webhook| C
    C -->|triggers| E
    E -->|dispatches| L
    L -->|sends| M
    L -->|sends| N
```

## 2. Payment System ERD (Chen Notation)

### Entities & Attributes

| Entity | Primary Key | Attributes |
|--------|-------------|------------|
| **PaymentTitle** | `id` | name, description, amount, type, academic_year_id |
| **Payment** | `id` | order_id, student_id, classroom_id, payment_title_id, gross_amount, email, payment_type, status, transaction_id, va_number, paid_at |
| **Charge** | `id` | payment_id, order_id, snap_token, gross_amount, transaction_status, transaction_id, payment_type |
| **StudentFee** | `id` | student_id, payment_title_id, amount, due_date, status, academic_year, notes |
| **Student** | `id` | nisn, name, phone, email, classroom_id |

### Relationships (Chen Notation)

```
Student (1,N) ──────< Payment >─────── (1,1) Classroom
Payment (1,N) ──────< PaymentTitle
Payment (1,1) ──────< Charge
Student (1,N) ──────< StudentFee
StudentFee (0,N) ───< PaymentTitle
```

### Cardinality Summary

| From | Relationship | To | Type |
|------|--------------|-----|------|
| Student | has many | Payment | 1:N |
| Student | has many | StudentFee | 1:N |
| Student | has many | WhatsAppConversation | 1:N |
| PaymentTitle | defines many | Payment | 1:N |
| PaymentTitle | defines many | StudentFee | 1:N |
| Payment | generates | Charge | 1:1 |
| Payment | belongs to | Classroom | N:1 |
| WhatsAppConversation | belongs to | Student | N:1 |
| WhatsAppConversation | has many | WhatsAppMessage | 1:N |

---

## 3. WhatsApp Gateway Flow Chart (Mermaid)

```mermaid
flowchart TD
    subgraph Meta_API
        WA[WhatsApp Meta API]
        WH[Webhook]
    end

    subgraph Application
        WC[WhatsAppConversation]
        WM[WhatsAppMessage]
        WT[WhatsAppMessageTemplate]
        
        subgraph Bot_Service
            WBS[WhatsAppBotService]
            WAR[WhatsAppAdminRouterService]
            WMS[WhatsappMetaService]
        end
        
        subgraph Chat_Service
            WCS[WhatsAppChatService]
        end
        
        subgraph Distribution
            RDS[RaporDistributionService]
        end
    end

    subgraph Admin
        AD[Admin/Staff]
    end

    WA -->|incoming| WH
    WH -->|process| WC
    WC -->|route| WAR
    WAR -->|inside hours| AD
    WAR -->|outside hours| WBS
    WBS -->|message| WMS
    WMS -->|send| WA
    RDS -->|process status| WM
    WCS -->|admin sends| WC
    AD -->|reply| WCS
```

## 4. WhatsApp Gateway ERD (Chen Notation)

### Entities & Attributes

| Entity | Primary Key | Attributes |
|--------|-------------|------------|
| **WhatsAppConversation** | `id` | phone_number, profile_name, student_id, assigned_admin_id, status, message_count, last_message_at |
| **WhatsAppMessage** | `id` | conversation_id, sender_type, sender_id, message_type, content, media_url, media_type, status, whatsapp_message_id, read_at |
| **WhatsAppMessageTemplate** | `id` | name, category, content, variables, is_active |
| **Student** | `id` | nisn, name, phone |
| **User** | `id` | name, email (admin) |

### Relationships (Chen Notation)

```
WhatsAppConversation (1,1) ──────< Student
WhatsAppConversation (0,1) ──────< User (Admin)
WhatsAppConversation (1,N) ──────< WhatsAppMessage
WhatsAppMessageTemplate (1,N) ──────< WhatsAppMessage
```

### System States (Bot)

| State | Description |
|-------|-------------|
| `new` | User never chatted |
| `menu` | Greeted, waiting for choice |
| `waiting_nisn` | Waiting for NISN input |
| `verified` | NISN verified successfully |

---

## 5. Complete Unified ERD (Mermaid)

```mermaid
erDiagram
    STUDENT {
        string id PK
        string nisn
        string name
        string phone
        string email
    }

    CLASSROOM {
        string id PK
        string name
        string classroom_type
    }

    PAYMENT_TITLE {
        string id PK
        string name
        text description
        decimal amount
        string type
    }

    PAYMENT {
        string id PK
        string order_id UK
        string student_id FK
        string classroom_id FK
        string payment_title_id FK
        decimal gross_amount
        string email
        string payment_type
        string status
        string transaction_id
        string va_number
        datetime paid_at
    }

    CHARGE {
        string id PK
        string payment_id FK
        string order_id UK
        string snap_token
        decimal gross_amount
        string transaction_status
        string transaction_id
        string payment_type
    }

    STUDENT_FEE {
        string id PK
        string student_id FK
        string payment_title_id FK
        decimal amount
        date due_date
        string status
        string academic_year
    }

    WHATSAPP_CONVERSATION {
        string id PK
        string phone_number UK
        string profile_name
        string student_id FK
        string assigned_admin_id FK
        string status
        int message_count
        datetime last_message_at
    }

    WHATSAPP_MESSAGE {
        string id PK
        string conversation_id FK
        string sender_type
        string sender_id
        string message_type
        text content
        string media_url
        string media_type
        string status
        string whatsapp_message_id
        datetime read_at
    }

    WHATSAPP_MESSAGE_TEMPLATE {
        string id PK
        string name
        string category
        text content
        json variables
        boolean is_active
    }

    USER {
        string id PK
        string name
        string email
    }

    STUDENT ||--o{ PAYMENT : "creates"
    STUDENT ||--o{ STUDENT_FEE : "has"
    STUDENT ||--o{ WHATSAPP_CONVERSATION : "linked"
    CLASSROOM ||--o{ PAYMENT : "contains"
    PAYMENT_TITLE ||--o{ PAYMENT : "defines"
    PAYMENT_TITLE ||--o{ STUDENT_FEE : "defines"
    PAYMENT ||--|| CHARGE : "generates"
    USER ||--o{ WHATSAPP_CONVERSATION : "assigns"
    WHATSAPP_CONVERSATION ||--o{ WHATSAPP_MESSAGE : "contains"
    WHATSAPP_MESSAGE_TEMPLATE ||--o{ WHATSAPP_MESSAGE : "provides"
```

---

## 6. Chen Notation Summary

### Payment Domain

```
                        ┌─────────────────┐
                        │   PaymentTitle  │
                        │─────────────────│
                        │ id (PK)         │
                        │ name            │
                        │ amount          │
                        │ type            │
                        └────────┬────────┘
                                 │ 1:N
                                 │
        ┌────────────────────────┼────────────────────────┐
        │                        │                        │
        ▼                        ▼                        ▼
┌───────────────┐      ┌───────────────┐      ┌───────────────┐
│    Student    │      │    Payment    │      │  StudentFee   │
│───────────────│      │───────────────│      │───────────────│
│ id (PK)       │◄─────│ student_id FK │      │ student_id FK │
│ nisn          │ 1:N  │ payment_title │──────│ payment_title │
│ name          │      │ gross_amount  │ 1:N  │ amount        │
│ phone         │      │ status        │      │ due_date      │
└───────────────┘      │ paid_at       │      │ status        │
                       └───────┬───────┘      └───────────────┘
                               │
                               │ 1:1
                               ▼
                       ┌───────────────┐
                       │    Charge     │
                       │───────────────│
                       │ id (PK)       │
                       │ order_id      │
                       │ snap_token    │
                       │ transaction_  │
                       │   status      │
                       └───────────────┘
```

### WhatsApp Domain

```
┌─────────────────────┐       ┌─────────────────────┐
│       Student       │       │        User         │
│─────────────────────│       │─────────────────────│
│ id (PK)            │       │ id (PK)            │
│ nisn               │       │ name               │
│ name               │       │ email              │
│ phone              │       └─────────┬───────────┘
└────────┬────────────┘                 │
         │ 1:N                           │ 1:N (assigned)
         ▼                               ▼
┌─────────────────────┐       ┌─────────────────────┐
│ WhatsAppConversation│       │ WhatsAppConversation│
│─────────────────────│       │─────────────────────│
│ id (PK)            │       │ id (PK)             │
│ phone_number       │       │ phone_number        │
│ profile_name       │       │ assigned_admin_id   │
│ student_id (FK)    │       │ status              │
│ status             │       └──────────┬──────────┘
└────────┬────────────┘                  │
         │ 1:N                           │
         ▼                               │
┌─────────────────────┐       ┌─────────────────────┐
│  WhatsAppMessage    │       │ WhatsAppMessage     │
│─────────────────────│       │─────────────────────│
│ id (PK)            │       │ id (PK)             │
│ conversation_id    │       │ conversation_id     │
│ sender_type        │       │ sender_type         │
│ content            │       │ content             │
│ status             │       │ status              │
└─────────────────────┘       └─────────────────────┘

┌─────────────────────┐
│ WhatsAppMessage    │
│ Template            │
│─────────────────────│
│ id (PK)            │
│ name               │
│ category           │
│ content            │
│ is_active          │
└─────────────────────┘
```

---

## 7. Status Values Reference

### Payment Status
| Status | Description |
|--------|-------------|
| `pending` | Waiting for payment |
| `partial` | Partial payment received |
| `completed` | Full payment received ✓ |
| `overdue` | Payment overdue |
| `failed` | Payment failed |
| `cancelled` | Payment cancelled |

### WhatsApp Conversation Status
| Status | Description |
|--------|-------------|
| `active` | Conversation active |
| `closed` | Conversation closed |
| `archived` | Conversation archived |

### WhatsApp Message Status
| Status | Description |
|--------|-------------|
| `sent` | Message sent to API |
| `delivered` | Delivered to user |
| `read` | Read by user |
| `failed` | Failed to send |
| `pending` | Waiting to send |