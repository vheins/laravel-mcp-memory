# Tech Stack: Odoo 19 (Enterprise/Community)

## 1. Core Stack
- **Platform**: Odoo 19.0+
- **Language**: Python 3.12+
- **Database**: PostgreSQL 17+
- **Frontend**: OWL 3.0 (Odoo Web Library)
- **XML**: QWeb Templates

## 2. Coding Rules
- **Manifest**: Strictly defined `__manifest__.py` with correct dependencies.
- **Models**: Use `Model`, `TransientModel`, `AbstractModel` correctly.
- **Security**: Mandatory `ir.model.access.csv` for new models.
- **Linting**: Pylint with `pylint-odoo` plugin.
- **Translations**: Use `_()` for all user-facing strings.

## 3. Architecture (Addons)
- **Structure**:
  - `/controllers`: HTTP/Web Controllers.
  - `/models`: DB Schemas & Business Logic.
  - `/views`: XML Views (Backend/Web).
  - `/security`: Access Rights & Rules.
  - `/static`: JS (OWL), CSS, Images.
  - `/wizards`: Transient models for interactions.

## 4. Testing
- **Framework**: `odoo.tests.common` (TransactionCase, HttpCase).
- **Tours**: JS Tours for UI testing.
