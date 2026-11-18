const formatarBRL = (valor) => new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
}).format(Number(valor) || 0);

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
                    : ['#e7e1d4'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '64%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                        padding: 14,
                        font: { family: "'DM Sans', sans-serif", size: 12 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) => categoriasComGasto.length
                            ? ` ${ctx.label}: ${formatarBRL(ctx.parsed)}`
                            : ' Nenhuma despesa registrada neste mês'
                    }
                }
            }
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
                    backgroundColor: '#15803d',
                    borderRadius: 6,
                    maxBarThickness: 28
                },
                {
                    label: 'Despesas',
                    data: meses.map(m => Number(m.despesas)),
                    backgroundColor: '#ef4444',
                    borderRadius: 6,
                    maxBarThickness: 28
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#efebe0' },
                    ticks: {
                        callback: (value) => formatarBRL(value)
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                        padding: 14,
                        font: { family: "'DM Sans', sans-serif", size: 12 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ` ${ctx.dataset.label}: ${formatarBRL(ctx.parsed.y)}`
                    }
                }
            }
        }
    });
}
