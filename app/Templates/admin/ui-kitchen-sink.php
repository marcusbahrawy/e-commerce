<?php
$title = $title ?? 'UI-komponenter';
?>
<div class="admin-ui-sink">
  <p class="text-muted" style="margin-bottom: var(--space-5);">Design system — alle komponenter og tilstander. Bruk tokens og disse klassene konsistent.</p>

  <section class="admin-ui-sink__section">
    <h2 class="h2" style="margin-bottom: var(--space-4);">Knapper</h2>
    <div style="display: flex; flex-wrap: wrap; gap: var(--space-3); align-items: center;">
      <button type="button" class="btn btn--primary">Primary</button>
      <button type="button" class="btn btn--secondary">Secondary</button>
      <button type="button" class="btn btn--accent">Accent</button>
      <button type="button" class="btn btn--ghost">Ghost</button>
      <button type="button" class="btn btn--primary" disabled>Disabled</button>
      <button type="button" class="btn btn--primary is-loading">Loading</button>
    </div>
  </section>

  <section class="admin-ui-sink__section">
    <h2 class="h2" style="margin-bottom: var(--space-4);">Inputs</h2>
    <div style="max-width: 400px;">
      <div class="form-group">
        <label for="sink-text">Tekst</label>
        <input type="text" id="sink-text" class="input" placeholder="Placeholder">
      </div>
      <div class="form-group">
        <label for="sink-email">E-post</label>
        <input type="email" id="sink-email" class="input" value="test@example.com">
      </div>
      <div class="form-group">
        <label for="sink-select">Select</label>
        <select id="sink-select" class="input">
          <option>Valg 1</option>
          <option>Valg 2</option>
        </select>
      </div>
      <div class="form-group">
        <label for="sink-textarea">Textarea</label>
        <textarea id="sink-textarea" class="input" rows="3">Tekst</textarea>
      </div>
      <div class="form-group">
        <label><input type="checkbox" checked> Checkbox</label>
      </div>
      <div class="form-group">
        <label><input type="radio" name="sink-r" checked> Radio A</label>
        <label><input type="radio" name="sink-r"> Radio B</label>
      </div>
      <div class="form-group">
        <label for="sink-error">Input med feil</label>
        <input type="text" id="sink-error" class="input is-error" value="Ugyldig">
        <span class="form-error">Dette feltet er påkrevd.</span>
      </div>
    </div>
  </section>

  <section class="admin-ui-sink__section">
    <h2 class="h2" style="margin-bottom: var(--space-4);">Cards, Badges, Alerts</h2>
    <div style="display: flex; flex-wrap: wrap; gap: var(--space-4);">
      <div class="card" style="width: 280px;">
        <div class="card__header">Card header</div>
        <div class="card__body">Innhold i kort.</div>
        <div class="card__footer">Footer</div>
      </div>
      <div style="display: flex; flex-wrap: wrap; gap: var(--space-2); align-items: center;">
        <span class="badge badge--primary">Primary</span>
        <span class="badge badge--accent">Accent</span>
        <span class="badge badge--success">Success</span>
        <span class="badge badge--warning">Warning</span>
        <span class="badge badge--error">Error</span>
        <span class="badge badge--neutral">Neutral</span>
      </div>
    </div>
    <div style="margin-top: var(--space-4); display: flex; flex-direction: column; gap: var(--space-3); max-width: 480px;">
      <div class="alert alert--success">Suksessmelding.</div>
      <div class="alert alert--warning">Advarsel.</div>
      <div class="alert alert--error">Feilmelding.</div>
      <div class="alert alert--info">Informasjon.</div>
    </div>
  </section>

  <section class="admin-ui-sink__section">
    <h2 class="h2" style="margin-bottom: var(--space-4);">Tabell</h2>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Kolonne A</th>
            <th>Kolonne B</th>
            <th class="table__actions">Handlinger</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Rad 1</td>
            <td>Verdi</td>
            <td class="table__actions">
              <a href="#">Rediger</a>
              <button type="button" class="btn btn--ghost btn--sm">Slett</button>
            </td>
          </tr>
          <tr>
            <td>Rad 2</td>
            <td>Verdi</td>
            <td class="table__actions"><a href="#">Rediger</a></td>
          </tr>
        </tbody>
      </table>
    </div>
    <nav class="pagination" aria-label="Paginering">
      <ul class="pagination__list">
        <li><span aria-current="page">1</span></li>
        <li><a href="#">2</a></li>
        <li><a href="#">3</a></li>
      </ul>
    </nav>
  </section>

  <section class="admin-ui-sink__section">
    <h2 class="h2" style="margin-bottom: var(--space-4);">Breadcrumbs</h2>
    <nav class="breadcrumbs" aria-label="Brødsmuler">
      <ol class="breadcrumbs__list">
        <li class="breadcrumbs__item"><a href="#">Hjem</a></li>
        <li class="breadcrumbs__item"><a href="#">Kategori</a></li>
        <li class="breadcrumbs__item" aria-current="page">Produkt</li>
      </ol>
    </nav>
  </section>

  <section class="admin-ui-sink__section">
    <h2 class="h2" style="margin-bottom: var(--space-4);">ProductCard, PriceTag, StockPill, FilterChip</h2>
    <div style="display: flex; flex-wrap: wrap; gap: var(--space-3); margin-bottom: var(--space-4);">
      <span class="price-tag">499 kr</span>
      <span class="price-tag price-tag--was">699 kr</span>
      <span class="stock-pill stock-pill--in">På lager</span>
      <span class="stock-pill stock-pill--low">Få igjen</span>
      <span class="stock-pill stock-pill--out">Utsolgt</span>
      <button type="button" class="filter-chip">Filter</button>
      <button type="button" class="filter-chip filter-chip--active">Aktivt filter</button>
    </div>
    <div class="product-card" style="width: 220px;">
      <a href="#" class="product-card__link">
        <div class="product-card__image-wrap">
          <div class="product-card__image product-card__image--placeholder"></div>
        </div>
        <h3 class="product-card__title">Eksempelprodukt</h3>
        <p class="product-card__price"><span class="price-tag">399 kr</span></p>
      </a>
      <div style="padding: 0 var(--space-4) var(--space-4);">
        <button type="button" class="btn btn--primary btn--block">Legg i handlekurv</button>
      </div>
    </div>
  </section>

  <section class="admin-ui-sink__section">
    <h2 class="h2" style="margin-bottom: var(--space-4);">Admin-kort (KPI)</h2>
    <div class="admin-cards">
      <div class="admin-card">
        <div class="admin-card__value">42</div>
        <div class="admin-card__label">Ordrer i dag</div>
      </div>
      <div class="admin-card">
        <div class="admin-card__value">128</div>
        <div class="admin-card__label">Produkter</div>
      </div>
    </div>
  </section>
</div>

<style>
.admin-ui-sink__section { margin-bottom: var(--space-8); }
.form-error { font-size: var(--fs-sm); color: var(--color-error); margin-top: var(--space-1); display: block; }
</style>
