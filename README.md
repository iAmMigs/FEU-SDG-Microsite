# FEU Institute of Technology SDG Microsite

The **FEU Institute of Technology SDG Microsite** is a centralized Symfony web platform and content management system built to showcase, archive, and measure student capstone/thesis projects, faculty research, and community extension initiatives aligned with the 17 United Nations Sustainable Development Goals (UN SDGs).

---

## Features

### Public Portal
- **Interactive Landing Page (`/`)**: Features a campus hero carousel, a toggleable SDG Focus Areas grid (switching between institutional focus SDGs and all 17 UN SDGs) with modal drill-downs to filtered projects and activities, an interactive marquee of *Featured Voices in Research*, and the latest news updates.
- **UN SDG Directory (`/sdgs`)**: Displays all 17 Sustainable Development Goals with active institutional focus-area indicators and modal previews linking directly to related studies and events.
- **Research & Project Library (`/library`)**: Paginated archive of academic theses, capstone projects, and publications supporting multi-criteria filtering by author, title, research year, and SDG tags—including both **inclusive** (matches at least one selected SDG) and **exclusive/precision** (matches exact SDG combinations) search modes.
- **Project Detail View (`/library/thesis/{id}`)**: Displays abstracts, metadata, college and publication type tags, DOI/external publication links, uploaded PDF access, an interactive one-click citation generator (**APA**, **IEEE**, and **MLA**), and session-throttled view tracking.
- **Trending Studies (`/trending`)**: Highlights the top 6 most-viewed active research projects across the platform.
- **News & Activities (`/news`)**: Filterable campus events and sustainability updates by SDG, college, preset time windows (last 1, 7, or 30 days, or past year), and custom date ranges, respecting scheduled release timestamps (`publishAt`).
- **Submission Guidelines (`/submit-project`)**: Dynamically renders tabbed checklists of submission requirements per category (projects, events/activities, etc.) configured from the Admin Console.
- **About & Legal Pages (`/about`, `/privacy-policy`, `/terms-of-service`)**: Institutional overview highlighting FEU Institute of Technology's core sustainability pillars—Academic Research, Community Extension, and Sustainable Innovation.

### Administration Console (`/admin`)
- **Role-Protected Access (`/admin/login`)**: Authenticated via Symfony Security (`ROLE_ADMIN`) with CSRF protection and automatic password hashing backed by the `Admin` entity.
- **Analytics Dashboard**:
  - Live summary cards for Active Projects, Total Published Projects, and News & Activities.
  - Interactive **Chart.js** visualizations for 6-month project submission trends and SDG-filterable Top 10 Most Viewed projects.
  - **Privacy-First Country Traffic Analytics**: Aggregated website traffic by country powered by `SiteVisitSubscriber`, using 15-minute session throttling without storing raw visitor IP addresses.
- **Project Library CRUD & Bulk Tools**:
  - Full management of projects, cover images, PDF documents, DOIs, external links, and SDG associations.
  - **CSV Bulk Import**: Batch-imports multiple project records with automatic delimiter detection (`,` or `;`), dynamic header mapping (`Authors, Title, Abstract, DOI, Type, College, SDG`), chunked Doctrine flushes, and automatic creation of new Colleges or Publication Types.
  - **Advanced Batch Management Modal**: Dual-panel batch tool to search/filter projects by keyword, SDG, year, publication type, or active status, and perform bulk activation, deactivation, or deletion.
- **Rich-Text News & Activities Authoring**: Integrates **TinyMCE 6** with asynchronous server-side image uploads (`/admin/upload-image`) and synchronized active/scheduled release publishing logic.
- **Taxonomy & Site Configuration**: EasyAdmin CRUD controllers for SDG Categories (active focus-area toggles), Activity Categories, Featured Voices in Research, Publication Types, Colleges, Requirement Categories (submission steps), and Administrator accounts.

---

## Tech Stack

- **Backend**: PHP 8.2+, Symfony 7.4, Doctrine ORM 3.6, Doctrine Migrations 3.7
- **Admin Interface**: EasyAdmin Bundle, Chart.js, TinyMCE 6
- **Frontend**: Twig, Tailwind CSS v4 (`symfonycasts/tailwind-bundle`), Symfony AssetMapper, Hotwire Stimulus (`@hotwired/stimulus`), Hotwire Turbo (`@hotwired/turbo`), jQuery 4
- **Database**: MariaDB 10.4+ / MySQL 8+ (`utf8mb4`)
- **Testing & Tooling**: PHPUnit 11.5, Symfony Web Profiler, Symfony Maker Bundle

---

## Project Structure

```text
FEU-SDG-Microsite/
├── assets/
│   ├── controllers/        # Stimulus controllers (carousel, modal, mobile menu, autosubmit, CSRF)
│   ├── styles/             # Tailwind CSS v4 entrypoint and custom FEU theme tokens/fonts
│   └── app.js              # AssetMapper JavaScript entrypoint
├── config/
│   ├── packages/           # Symfony, Doctrine, Security, Tailwind, and Turbo configuration
│   └── routes/             # EasyAdmin and framework routing configuration
├── migrations/             # Doctrine database migrations
├── public/
│   ├── fonts/              # Self-hosted typography assets
│   ├── images/             # Campus photography, branding, and UN SDG icon sets
│   ├── js/                 # Admin helper scripts
│   └── uploads/            # User/admin uploaded files (theses, activities, voices)
├── src/
│   ├── Controller/
│   │   ├── Admin/          # EasyAdmin dashboard, CRUD controllers, security, and image uploader
│   │   └── *.php           # Public portal controllers (Home, SDG, Thesis, News, Trending, etc.)
│   ├── Entity/             # Doctrine ORM entities (Thesis, Activity, Sdg, Admin, CountryVisit, etc.)
│   ├── EventSubscriber/    # SiteVisitSubscriber for anonymized country-level analytics
│   ├── Form/               # Symfony Form types
│   └── Repository/         # Doctrine repositories
├── templates/
│   ├── Admin-Microsite/    # Custom EasyAdmin dashboard, login, thesis batch UI, and field templates
│   └── SDG-Microsite/      # Public microsite Twig layouts, pages, and partials
└── tests/                  # PHPUnit bootstrap and test suite
```

---

## Getting Started

### Prerequisites
- **PHP** `>= 8.2` with extensions `ctype`, `iconv`, `pdo_mysql`, `mbstring`, `intl`, and `xml`
- **Composer**
- **MariaDB** `10.4+` or **MySQL** `8.0+`
- **Symfony CLI** (recommended for local development) or an Apache/Nginx web server

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/iAmMigs/FEU-SDG-Microsite.git
   cd FEU-SDG-Microsite
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Configure local environment variables**
   Create an uncommitted `.env.local` file in the project root to override defaults without committing secrets:
   ```dotenv
   APP_ENV=dev
   APP_SECRET=<generate-a-secure-random-secret>
   DATABASE_URL="mysql://<db_user>:<db_password>@127.0.0.1:3306/<db_name>?serverVersion=10.4.0-MariaDB&charset=utf8mb4"
   MAILER_DSN=null://null
   ```

4. **Create the database and run migrations**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. **Install AssetMapper packages and build Tailwind CSS**
   ```bash
   php bin/console importmap:install
   php bin/console tailwind:build
   ```
   During active development, you can watch for CSS changes with:
   ```bash
   php bin/console tailwind:build --watch
   ```

6. **Ensure upload directories are writable**
   Verify that the web server has write permissions for:
   - `public/uploads/theses/`
   - `public/uploads/activities/`
   - `public/uploads/voices/`

7. **Create an initial Administrator account**
   Generate a password hash using Symfony's security command:
   ```bash
   php bin/console security:hash-password
   ```
   Then insert an initial record into the `admin` table (`employee_id`, `full_name`, `email`, `roles` as `'["ROLE_ADMIN"]'`, and the hashed `password`). Once signed in at `/admin/login`, additional administrators can be managed directly through the **Administrators** section in the Admin Console.

8. **Start the local development server**
   ```bash
   symfony server:start
   ```
   Or using PHP's built-in server:
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

---

## CSV Bulk Project Import Format

From **Admin Console → Project Library → Add Multiple Projects**, administrators can upload `.csv` files (comma- or semicolon-delimited) with the following header row:

```csv
Authors,Title,Abstract,DOI,Type,College,SDG
```

- **`Authors`** and **`Title`** are required columns. Separate multiple authors with semicolons (`;`).
- **`SDG`** accepts comma-separated SDG numbers (e.g., `"4, 9, 13"`).
- **`Type`** and **`College`** values are automatically matched or created if they do not yet exist.
- All imported projects default to **inactive** so administrators can review and activate them individually or via the batch management modal.

---

## Running Tests

Execute the PHPUnit test suite with:

```bash
php bin/phpunit
```

---

## Routes Reference

| Route | Access | Description |
| :--- | :--- | :--- |
| `/` | Public | Landing page (hero carousel, SDG focus areas, featured voices, latest updates) |
| `/sdgs` | Public | Interactive directory of all 17 UN SDGs |
| `/library` | Public | Filterable research & project library |
| `/library/thesis/{id}` | Public | Individual project details, abstract, PDF view, and APA/IEEE/MLA citations |
| `/trending` | Public | Top 6 most-viewed research projects |
| `/news` | Public | Filterable campus news & sustainability activities |
| `/news/article/{id}` | Public | Full news/activity article view |
| `/submit-project` | Public | Dynamic project and event submission requirements |
| `/about` | Public | About the microsite and institutional sustainability pillars |
| `/privacy-policy`, `/terms-of-service` | Public | Privacy Policy and Terms of Service pages |
| `/admin/login` | Public | Administrator login page |
| `/admin` | `ROLE_ADMIN` | EasyAdmin analytics dashboard and content management console |

---

## License

Proprietary — developed for **FEU Institute of Technology**. All rights reserved.

