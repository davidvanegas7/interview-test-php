# PHP 8 + Composer with Docker

This is a minimal Docker-based setup for running PHP 8 with Composer using plain `docker run`—no Docker Compose.

---

## 🚀 Features

- PHP 8.2 (CLI)
- Composer for dependency management

---

## 🛠 Requirements

- [Docker](https://www.docker.com/)

---

## ⚙️ Setup & Usage

1. **Build the Docker image:**

   ```bash
   docker build -t php-transform .
   ```

2. **Use Composer inside the container:**

   To install packages:

   ```bash
   docker run --rm -it -v "$PWD":/app -w /app php-transform composer require vendor/package
   ```

---

## 📦 Installing PHP Extensions

If you need to install additional PHP libraries (e.g., `pdo`, `mbstring`, etc.), modify the `Dockerfile` like so:

```Dockerfile
RUN docker-php-ext-install pdo pdo_mysql
```

Then rebuild the image:

```bash
docker build -t php-transform .
```

---

## 🧼 Cleaning Up

Docker automatically cleans up after the container runs using `--rm`.

To remove the Docker image when you're done:

```bash
docker rmi php-transform
```

---

## 🧪 Senior PHP Challenge: CSV to JSON Data Transformer

### Overview

You are tasked with building a PHP CLI application that:

- Reads a large CSV file with user data.
- Applies a series of data transformations.
- Writes the transformed data efficiently to a JSON file.

---

### Requirements

1. **Input CSV format:**

   Columns: `id, first_name, last_name, email, signup_date, amount_spent, country_code`

   Example row:

   ```csv
   1234,John,Doe,john@example.com,2023-11-15,250.50,US
   ```

2. **Transformations:**

   - Normalize names (trim, capitalize).
   - Validate email format; discard invalid emails.
   - Convert `signup_date` to ISO 8601 UTC datetime string.
   - Round `amount_spent` to 2 decimals; filter out entries with `< 10`.
   - Map `country_code` to full country name from `countries.json`.
   - Add `loyalty_level` based on `amount_spent`:
     - `< 100`: Bronze
     - `100 - 500`: Silver
     - `> 500`: Gold

3. **Output:**

   - Write all valid transformed entries to `output.json`.
   - Output must be a valid JSON array.
   - Use streaming/generators to keep memory usage low.

---

### Technical Expectations

- Use PHP 8+ features (typed properties, union types, constructor promotion, readonly, match, nullsafe operator, etc.).
- Follow SOLID principles and clean OOP design.
- Handle malformed input gracefully.
- Use strict typing.
- Avoid loading entire files into memory.
- Unit tests for transformation classes.

---

### Bonus (Optional)

- CLI options to specify input/output files.
- Use advanced PHP 8 features like Fibers, WeakMap, Attributes, Enums.
- Use composer packages to simplify the work.

---

### 🏃‍♂️ How to Run the CLI Script

1. Place your input CSV file in the project directory (e.g., `input.csv`).

2. Run the script using Docker:

   ```bash
   docker run --rm \
    -v "$PWD":/app \
    -w /app \
    -v "$PWD/xdebug-profiles":/tmp/xdebug \
    php-transform \
    php src/bin/transform.php --input=app/input.csv --output=app/output.json
   ```

3. Check `output.json` for the transformed data.
