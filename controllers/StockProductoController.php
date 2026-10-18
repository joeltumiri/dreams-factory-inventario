<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Producto;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

final class StockProductoController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['@'],
                        'matchCallback' => static function (): bool {
                            $user = Yii::$app->user->identity;
                            return $user && ($user->isGerente() || $user->isEncargado() || $user->isVendedor());
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['exportar-excel', 'exportar-pdf', 'orden-sugerida'],
                        'roles' => ['@'],
                        'matchCallback' => static function (): bool {
                            $user = Yii::$app->user->identity;
                            return $user && ($user->isGerente() || $user->isEncargado());
                        },
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string|Response
     */
    public function actionIndex(string $tab = 'disponible')
    {
        $tab = $this->normalizeTab($tab);

        $query = $this->buildQuery($tab);

        $dataProvider = new ActiveDataProvider([
            'query'      => clone $query,
            'pagination' => ['pageSize' => 25],
            'sort'       => false,
        ]);

        // Counts for tab badges
        $counts = [
            'disponible' => $this->buildQuery('disponible')->count(),
            'alerta'     => $this->buildQuery('alerta')->count(),
            'bajo'       => $this->buildQuery('bajo')->count(),
        ];

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'tab'          => $tab,
            'counts'       => $counts,
        ]);
    }

    /**
     * Exporta el listado de la pestaña activa a CSV (compatible con Excel).
     */
    public function actionExportarExcel(string $tab = 'disponible'): Response
    {
        $tab    = $this->normalizeTab($tab);
        $items  = $this->buildQuery($tab)->all();
        $titulo = $this->tituloTab($tab);

        $resp = Yii::$app->response;
        $resp->format = Response::FORMAT_RAW;
        $resp->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $resp->headers->set('Content-Disposition',
            'attachment; filename="stock_' . $tab . '_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://temp', 'w+');
        // BOM para Excel UTF-8
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ["Reporte: $titulo - " . date('d/m/Y H:i')]);
        fputcsv($out, []);
        fputcsv($out, ['Código', 'Categoría', 'Producto', 'Stock Mínimo', 'Stock Actual']);

        foreach ($items as $p) {
            fputcsv($out, [
                (string)($p->codigo ?? 'BG-P' . $p->id),
                $p->categoria ? $p->categoria->nombre : '—',
                $p->nombre,
                (int)$p->stock_minimo,
                (int)$p->stock,
            ]);
        }

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        $resp->content = $csv;
        return $resp;
    }

    /**
     * Vista imprimible (PDF via diálogo de impresión del navegador).
     */
    public function actionExportarPdf(string $tab = 'disponible'): string
    {
        $tab    = $this->normalizeTab($tab);
        $items  = $this->buildQuery($tab)->all();
        $titulo = $this->tituloTab($tab);

        $this->layout = false;
        return $this->render('print', [
            'items'  => $items,
            'titulo' => $titulo,
            'tab'    => $tab,
        ]);
    }

    /**
     * Genera una orden de compra sugerida (CSV) para productos en alerta o bajo stock.
     * Cantidad sugerida = max(stock_minimo*2 - stock_actual, stock_minimo).
     */
    public function actionOrdenSugerida(): Response
    {
        $items = Producto::find()
            ->with(['categoria'])
            ->andWhere(['activo' => 1])
            ->andWhere(new Expression('stock <= stock_minimo * 1.2'))
            ->orderBy(['stock' => SORT_ASC])
            ->all();

        $resp = Yii::$app->response;
        $resp->format = Response::FORMAT_RAW;
        $resp->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $resp->headers->set('Content-Disposition',
            'attachment; filename="orden_compra_sugerida_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://temp', 'w+');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Orden de Compra Sugerida - ' . date('d/m/Y H:i')]);
        fputcsv($out, []);
        fputcsv($out, [
            'Código', 'Categoría', 'Producto',
            'Stock Mínimo', 'Stock Actual', 'Cantidad Sugerida',
            'Precio Compra Ref.', 'Subtotal Estimado'
        ]);

        $totalEstimado = 0.0;
        foreach ($items as $p) {
            $stock    = (int)$p->stock;
            $minimo   = (int)$p->stock_minimo;
            $sugerida = max(($minimo * 2) - $stock, $minimo > 0 ? $minimo : 1);
            $precio   = (float)$p->precio_compra;
            $subtotal = $sugerida * $precio;
            $totalEstimado += $subtotal;

            fputcsv($out, [
                (string)($p->codigo ?? 'BG-P' . $p->id),
                $p->categoria ? $p->categoria->nombre : '—',
                $p->nombre,
                $minimo,
                $stock,
                $sugerida,
                number_format($precio, 2, '.', ''),
                number_format($subtotal, 2, '.', ''),
            ]);
        }
        fputcsv($out, []);
        fputcsv($out, ['', '', '', '', '', '', 'TOTAL ESTIMADO', number_format($totalEstimado, 2, '.', '')]);

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        $resp->content = $csv;
        return $resp;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function normalizeTab(string $tab): string
    {
        return in_array($tab, ['disponible', 'alerta', 'bajo'], true) ? $tab : 'disponible';
    }

    private function tituloTab(string $tab): string
    {
        return [
            'disponible' => 'A Disposición',
            'alerta'     => 'Alerta Stock',
            'bajo'       => 'Bajo Stock',
        ][$tab];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    private function buildQuery(string $tab)
    {
        $q = Producto::find()
            ->with(['categoria'])
            ->andWhere(['activo' => 1])
            ->orderBy(['stock' => SORT_ASC, 'nombre' => SORT_ASC]);

        if ($tab === 'disponible') {
            // Por encima del mínimo (más allá de la franja de alerta del 20%)
            $q->andWhere(new Expression('stock > stock_minimo * 1.2'));
        } elseif ($tab === 'alerta') {
            // Próximo al mínimo: entre el mínimo y +20% del mínimo
            $q->andWhere(new Expression('stock > stock_minimo'))
              ->andWhere(new Expression('stock <= stock_minimo * 1.2'));
        } else { // bajo
            $q->andWhere(new Expression('stock <= stock_minimo'));
        }

        return $q;
    }
}
