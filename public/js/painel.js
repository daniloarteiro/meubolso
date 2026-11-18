// Gráfico de gastos por categoria
const categoriasComGasto = (dados.categorias || []).filter(c => Number(c.gasto) > 0);
const canvasCategorias = document.getElementById('graficoCategorias');

if (canvasCategorias) {
    new Chart(canvasCategorias, {
        type: 'doughnut',
        data: {
            labels: categoriasComGasto.length ? categoriasComGasto.map(c => c.nome) : ['Sem gastos no mês'],
            datasets: [{
                data: categoriasComGasto.length ? categoriasComGasto.map(c => Number(c.gasto)) : [1],
                backgroundColor: categoriasComGasto.length
                    ? categoriasComGasto.map(c => c.cor || '#15803d')
                    : ['#e2e8f0']
            }]
        },
        options: {
            plugins: { legend: { position: 'bottom' } }
        }
    });
}

// Gráfico de receitas x despesas nos últimos meses
const canvasMeses = document.getElementById('graficoMeses');
const meses = dados.meses || [];

if (canvasMeses) {
    new Chart(canvasMeses, {
        type: 'bar',
        data: {
            labels: meses.map(m => m.label),
            datasets: [
                {
                    label: 'Receitas',
                    data: meses.map(m => Number(m.receitas)),
                    backgroundColor: '#15803d'
                },
                {
                    label: 'Despesas',
                    data: meses.map(m => Number(m.despesas)),
                    backgroundColor: '#f87171'
                }
            ]
        },
        options: {
            plugins: { legend: { position: 'bottom' } }
        }
    });
}
