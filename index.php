<?php
/**
 * Calculadora de Distribución de Poisson - P(X=k), P(X≤k), P(X>k)
 */
header('Content-Type: text/html; charset=utf-8');

$lambda = $k = '';
$probK = null; $probMenorIgual = null; $probMayor = null;
$media = null; $varianza = null; $desvEstandar = null;
$tabla = null;

function poissonPmf($k, $lambda) {
    if ($k < 0 || $lambda <= 0) return 0.0;
    return exp(-$lambda) * pow($lambda, $k) / gmp_strval(gmp_fact($k));
}
function poissonCdf($k, $lambda) {
    $suma = 0.0;
    for ($i = 0; $i <= $k; $i++) {
        $suma += exp(-$lambda) * pow($lambda, $i) / gmp_strval(gmp_fact($i));
    }
    return $suma;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lambda = (float)($_POST['lambda'] ?? 0);
    $k      = (int)($_POST['k'] ?? -1);

    if ($lambda > 0 && $k >= 0) {
        $probK = poissonPmf($k, $lambda);
        $probMenorIgual = poissonCdf($k, $lambda);
        $probMayor = 1 - $probMenorIgual;
        $media = $lambda;
        $varianza = $lambda;
        $desvEstandar = sqrt($lambda);

        // Tabla de probabilidad para k=0 hasta max(k+5, 10)
        $maxK = max($k + 5, (int)ceil($lambda + 3 * sqrt($lambda)), 10);
        $tabla = [];
        $acum = 0;
        for ($i = 0; $i <= $maxK; $i++) {
            $p = poissonPmf($i, $lambda);
            $acum += $p;
            $tabla[] = ['k' => $i, 'p' => $p, 'acum' => $acum];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Calculadora Distribución de Poisson Online | ConfiguroWeb</title>
<meta name="description" content="Calcula probabilidades de la distribución de Poisson: P(X=k), P(X≤k), P(X>k). Media, varianza y tabla de probabilidad. Gratis en ConfiguroWeb.">
<meta name="keywords" content="distribucion poisson, probabilidad, estadistica, poisson calculator, lambda">
<meta property="og:type" content="website">
<meta property="og:title" content="Calculadora Distribución de Poisson Online">
<meta property="og:description" content="Calcula probabilidades de la distribución de Poisson.">
<link rel="canonical" href="https://demoscweb.com/github/php-calculadora-poisson/">
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"WebApplication","name":"Calculadora Distribución de Poisson","applicationCategory":"UtilitiesApplication","operatingSystem":"Any","offers":{"@type":"Offer","price":"0","priceCurrency":"USD"},"author":{"@type":"Person","name":"ConfiguroWeb","url":"https://configuroweb.com"}}
</script>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
  <h1>📊 Distribución de Poisson</h1>
  <p class="subtitle">Calcula probabilidades de eventos discretos</p>
</header>
<main>
  <form method="POST">
    <label for="lambda">λ (tasa media de ocurrencia)</label>
    <input type="number" name="lambda" id="lambda" step="0.001" min="0.001" value="<?php echo htmlspecialchars($lambda); ?>" placeholder="3.5" required>
    <small>Ej: si en promedio ocurren 3.5 eventos por hora, λ = 3.5</small>

    <label for="k">k (número de eventos)</label>
    <input type="number" name="k" id="k" step="1" min="0" value="<?php echo htmlspecialchars($k); ?>" placeholder="2" required>
    <small>Número exacto de eventos que quieres evaluar</small>

    <button type="submit" class="btn-primary">📊 Calcular probabilidad</button>
  </form>

  <?php if ($probK !== null): ?>
  <div class="resultados">
    <h2>Resultados</h2>
    <div class="tarjeta-destacada">
      <span class="etiqueta">P(X = <?php echo $k; ?>)</span>
      <span class="valor-grande"><?php echo number_format($probK * 100, 4); ?>%</span>
    </div>
    <div class="grid-3">
      <div class="tarjeta-sm">
        <span class="etiqueta">P(X ≤ <?php echo $k; ?>)</span>
        <span class="valor-sm"><?php echo number_format($probMenorIgual * 100, 4); ?>%</span>
      </div>
      <div class="tarjeta-sm">
        <span class="etiqueta">P(X > <?php echo $k; ?>)</span>
        <span class="valor-sm"><?php echo number_format($probMayor * 100, 4); ?>%</span>
      </div>
      <div class="tarjeta-sm">
        <span class="etiqueta">P(X < <?php echo $k; ?>)</span>
        <span class="valor-sm"><?php echo number_format(($probMenorIgual - $probK) * 100, 4); ?>%</span>
      </div>
    </div>

    <div class="grid-3" style="margin-top:1rem">
      <div class="tarjeta-sm">
        <span class="etiqueta">Media (μ)</span>
        <span class="valor-sm"><?php echo number_format($media, 4); ?></span>
      </div>
      <div class="tarjeta-sm">
        <span class="etiqueta">Varianza (σ²)</span>
        <span class="valor-sm"><?php echo number_format($varianza, 4); ?></span>
      </div>
      <div class="tarjeta-sm">
        <span class="etiqueta">Desviación estándar (σ)</span>
        <span class="valor-sm"><?php echo number_format($desvEstandar, 4); ?></span>
      </div>
    </div>

    <p class="interpretacion">
      📊 Con λ = <strong><?php echo $lambda; ?></strong>, la probabilidad de que ocurran
      exactamente <strong><?php echo $k; ?></strong> eventos es del
      <strong><?php echo number_format($probK * 100, 4); ?>%</strong>.
      La probabilidad de que ocurran <strong>hasta <?php echo $k; ?></strong> eventos es del
      <strong><?php echo number_format($probMenorIgual * 100, 2); ?>%</strong>.
    </p>

    <h3 style="margin-top:1.5rem">Tabla de distribución</h3>
    <div style="overflow-x:auto">
      <table style="width:100%;border-collapse:collapse;font-size:0.9rem">
        <thead>
          <tr style="background:#f0f0f0">
            <th style="padding:0.5rem;border:1px solid #ddd">k</th>
            <th style="padding:0.5rem;border:1px solid #ddd">P(X=k)</th>
            <th style="padding:0.5rem;border:1px solid #ddd">P(X≤k)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tabla as $fila): ?>
          <tr style="<?php echo $fila['k'] == $k ? 'background:#e3f2fd;font-weight:bold' : ''; ?>">
            <td style="padding:0.4rem;border:1px solid #ddd;text-align:center"><?php echo $fila['k']; ?></td>
            <td style="padding:0.4rem;border:1px solid #ddd;text-align:right"><?php echo number_format($fila['p'] * 100, 4); ?>%</td>
            <td style="padding:0.4rem;border:1px solid #ddd;text-align:right"><?php echo number_format($fila['acum'] * 100, 4); ?>%</td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <section class="info">
    <h2>¿Qué es la distribución de Poisson?</h2>
    <p>La distribución de Poisson modela el número de eventos que ocurren en un intervalo fijo de tiempo o espacio, cuando los eventos ocurren a una tasa media constante y de forma independiente. Ejemplos: llamadas a un call center por hora, defectos por metro cuadrado, accidentes por mes.</p>
    <p class="formula">P(X=k) = (e<sup>−λ</sup> × λ<sup>k</sup>) / k!</p>
    <p>Media = λ, Varianza = λ (ambas iguales a λ)</p>
  </section>
</main>
<footer>
  <p>Desarrollado por <a href="https://configuroweb.com" target="_blank">ConfiguroWeb</a> ·
     <a href="https://appscweb.com/citas/" target="_blank">Sistema de Citas</a> ·
     <a href="https://appscweb.com/negocios/" target="_blank">Gestión de Negocios</a></p>
  <p>&copy; <?php echo date('Y'); ?> ConfiguroWeb</p>
</footer>
<script src="assets/script.js"></script>
</body>
</html>