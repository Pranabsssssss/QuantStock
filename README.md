# QuantStock — Alternate Prototype

> An alternate frontend variation of QuantStock developed during the 24-hour AAROH hackathon development sprint.

## About

This repository contains an **alternate version of QuantStock** that I developed while working on the project for **AAROH**, a 24-hour hackathon held at **IIIT-Delhi**.

During the hackathon, we initially decided to develop **two different variations of the product** in parallel. The idea was to experiment with different implementations and approaches and eventually submit whichever version turned out to be stronger.

This repository is one of those variations.

However, this version was **not ultimately submitted to the hackathon**.

The other variation was developed further and became the actual submission. That version is available in the main full-stack repository:

**[QuantStock — Full Stack](https://github.com/Pranabsssssss/quantstock-full)**

---

# Hackathon Context

🏆 **Event:** AAROH Hackathon  
⏱️ **Development Time:** ~24 hours  
📍 **Venue:** IIIT-Delhi  
🥈 **Final Result:** 2nd Place

The project was developed under an extremely limited time constraint. The goal was to quickly experiment with ideas, build working features, and have a strong enough product ready for judging.

Rather than immediately committing to a single implementation, we experimented with multiple variations.

This repository represents **one of those experiments**.

---

# Why Two Versions?

At the beginning of development, we decided to build two different versions of QuantStock.

The reasoning was simple:

```text
                    QuantStock Idea
                          │
              ┌───────────┴───────────┐
              │                       │
         Variation 1             Variation 2
              │                       │
        Different UI             Different UI
        Different UX             Different UX
        Different ideas          Different ideas
              │                       │
              └───────────┬───────────┘
                          │
                    Compare Results
                          │
                          ▼
                   Better Version
                          │
                          ▼
                   Hackathon Demo
```

This repository is the **variation that wasn't selected for the final submission**.

The other implementation evolved into the much larger full-stack version containing:

- Frontend
- Backend
- REST APIs
- Database
- Inventory management
- POS
- Sales
- Supplier management
- Analytics
- AI/ML features

That version can be found here:

**https://github.com/Pranabsssssss/quantstock-full**

---

# What This Repository Contains

Unlike the final full-stack repository, this project is primarily focused on the **frontend experience and product interface**.

The repository contains:

- Next.js application
- React components
- TypeScript
- Dashboard UI
- Inventory-oriented interfaces
- Product management interfaces
- Business management UI
- API integration structure
- Reusable UI components
- State/context management
- Data visualization

The frontend is designed around a premium dark/glassmorphism visual style.

---

# Tech Stack

- **Next.js** — App Router
- **React 19**
- **TypeScript**
- **Tailwind CSS**
- **TanStack Query**
- **Axios**
- **React Hook Form**
- **Zod**
- **Framer Motion**
- **Recharts**
- **next-themes**

The repository currently contains dedicated directories for API integration, components, contexts, hooks, libraries, providers, services, and types.

---

# Project Structure

```text
QuantStock/
│
├── api/
├── app/
├── components/
├── contexts/
├── hooks/
├── lib/
├── providers/
├── public/
├── services/
├── types/
│
├── next.config.ts
├── package.json
├── tsconfig.json
├── tailwind.config.js
├── eslint.config.mjs
└── README.md
```

---

# Frontend Architecture

The frontend was designed around a modular Next.js architecture.

```text
                    Next.js App
                        │
        ┌───────────────┼────────────────┐
        │               │                │
      Pages         Components       Providers
        │               │                │
        └───────────────┼────────────────┘
                        │
                  Service Layer
                        │
                     API Client
                        │
                        ▼
                  Backend APIs
```

The repository separates API communication into the API/client and service layers rather than directly coupling UI components to backend requests.

---

# API Integration

The frontend was built to communicate with a backend through API services.

API requests are routed through:

```text
api/client.ts
services/*.service.ts
```

The UI is designed to handle:

- Loading states
- Empty states
- Error states
- Backend responses

The repository does not rely on mocked data for its API-driven UI.

---

# Design

The visual direction of this variation was based around a:

**Dark + Glassmorphism + Premium Dashboard**

design language.

The goal was to make the inventory/business management platform feel less like a traditional enterprise dashboard and more like a modern SaaS product.

The interface makes extensive use of:

- Dark UI
- Glassmorphism
- Cards
- Data visualization
- Motion
- Responsive layouts
- Modern typography
- Interactive dashboard components

---

# This Is Not the Final Submission

It is important to understand the relationship between this repository and the final QuantStock project.

### This repository

**Alternate prototype / frontend variation**

```text
QuantStock
   │
   ├── Frontend
   ├── UI experiments
   ├── UX experiments
   └── API integration structure
```

### Final submission

**Full-stack QuantStock**

```text
QuantStock Full
   │
   ├── Frontend
   ├── Backend
   ├── REST APIs
   ├── Database
   ├── Migrations
   ├── Inventory
   ├── POS
   ├── Sales
   ├── Suppliers
   ├── Analytics
   └── AI / ML
```

The final implementation is available at:

**https://github.com/Pranabsssssss/quantstock-full**

---

# Hackathon Development Reality

This project was developed during a **24-hour hackathon sprint**.

As a result, this repository should be viewed as a **prototype and development artifact**, rather than a polished production application.

The purpose of maintaining this repository is to preserve the alternate approach that was explored during development.

It shows one of the directions QuantStock could have taken before we decided which implementation to submit.

---

# Getting Started

## Clone

```bash
git clone https://github.com/Pranabsssssss/QuantStock.git

cd QuantStock
```

## Install Dependencies

```bash
npm install
```

## Environment

Create the local environment file:

```bash
cp .env.example .env.local
```

Configure the FastAPI/backend base URL in `.env.local`.

## Run

```bash
npm run dev
```

Then open the local Next.js development URL.

---

# Relationship With the Final Project

This repository and `quantstock-full` are part of the same QuantStock development story.

```text
                   QuantStock
                       │
             ┌─────────┴─────────┐
             │                   │
      Alternate Version      Final Version
             │                   │
       This Repository       quantstock-full
             │                   │
       Frontend-focused       Full Stack
             │                   │
             └─────────┬─────────┘
                       │
                 AAROH Hackathon
                       │
                    2nd Place
```

---

# Final Note

This repository was **not the version submitted at AAROH**.

It is preserved as an example of the parallel development and experimentation that happened during the hackathon.

The final submitted variation became significantly more complete and evolved into the full-stack QuantStock implementation.

**Final submission:**  
https://github.com/Pranabsssssss/quantstock-full

---

## Author

**Pranab Saini**

GitHub:  
https://github.com/Pranabsssssss

---

<p align="center">
  <strong>QuantStock</strong><br>
  Alternate Prototype • AAROH Hackathon • 2nd Place
</p>
