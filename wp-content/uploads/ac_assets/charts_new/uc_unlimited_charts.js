class UeCharts {
    constructor(options) {
        this.canvasContainer = options.canvasContainer
        this.canvas = options.canvas
        this.canvasCtx = this.canvas.getContext('2d')
        this.globalChartType = options.globalChartType
        this.chartOptions = options.chartOptions
        this.chartData = options.chartData


        this.createChart()
    }

    createChart = () => {



        this.chart = new Chart(this.canvasCtx,{
            type: this.globalChartType,
            data: this.chartData,
			options: this.chartOptions
        })
    }

    
}