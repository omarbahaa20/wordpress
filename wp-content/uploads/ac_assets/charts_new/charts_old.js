function UCChartsNew() {
  var g_objItems, g_objAttributes;
  var g_objWrapper;

  class ChartFactory {
    constructor(type) {
      switch (type) {
        case "bar":
          return new BarChart();
        case "line":
          return new LineChart();
        default:
          return new BaseChart();
      }
    }
  }

  class BaseChart {
    getDatasetOptionsFromItems() {
      const arrLabels = [];
      const arrData = [];
      const arrBackgroundColors = [];

      g_objItems.forEach(({ title, amount, color }) => {
        arrLabels.push(title);
        arrData.push(amount);
        arrBackgroundColors.push(color);
      });

      const objData = {
        labels: arrLabels,
        data: arrData,
        backgroundColor: arrBackgroundColors,
        
      };
      return objData;
    }

    getDatasetOptionsFromSettings() {
      const { border_width, border_color, label } = g_objAttributes;
      const data = {
        borderWidth: border_width,
        borderColor: border_color,
        label,
      };
      return data;
    }
    /**
     * convert chart data
     */
    getChartDatasets() {
      const { labels, data, backgroundColor } =
        this.getDatasetOptionsFromItems();

      const objDataset = {
        data,
        backgroundColor,
        ...this.getDatasetOptionsFromSettings(),
      };

      const objData = {
        labels,
        datasets: [objDataset],
      };

      return objData;
    }

	/**
	 * turn string value ("true", "false") to string 
	 */
	strToBool(str){
		
		switch(typeof str){
			case "boolean":
				return(str);
			break;
			case "undefined":
				return(false);
			break;
			case "number":
				if(str == 0)
					return(false);
				else 
					return(true);
			break;
			case "string":
				str = str.toLowerCase();
						
				if(str == "true" || str == "1")
					return(true);
				else
					return(false);
				
			break;
		}
		
		return(false);
	};
    
    
    /**
     * get chart options
     */
    getChartOptions() {
      const { begin_at_zero_x, 
    	  begin_at_zero_y, 
    	  legend_position,
    	  show_legend,
    	  show_grid_lines} = g_objAttributes;
          	  
      const options = {};
      const plugins = {};
       
      const scales = {
        x: {
          display:this.strToBool(show_grid_lines),
          beginAtZero: this.strToBool(begin_at_zero_x),
        },
        y: {
          display:this.strToBool(show_grid_lines),
          beginAtZero: this.strToBool(begin_at_zero_y),
        },
      };
      
      const tooltip = this._getTooltipOptions();
      const interaction = this._getInteractionsOptions();
      const animations = this._getAnimationOptions();
      const legendFont = this._getLegendFontOptions();
      
      const labels = {};
      labels.font = legendFont;
      
      //set plugins
	  plugins.legend = {
		   display:this.strToBool(show_legend),
    	   position: legend_position,
    	   labels:labels
      };
	  
      plugins.tooltip = tooltip;

      options.maintainAspectRatio = false;
      options.responsive = true;
      options.plugins = plugins;
      options.scales = scales;
      options.interaction = interaction;
      options.animations = animations;
      return options;
    }
    
    /**
     * get legend font options
     */
    _getLegendFontOptions(){
    	
    	const {
    		label_font_size,
    		label_font_weight,
    		label_font_style
    	} = g_objAttributes;
    	
    	var font = {};
    	    	
    	if(label_font_size)
    		font.size = label_font_size;
    	
    	if(label_font_weight && label_font_weight != "normal")
    		font.weight = label_font_weight;
    	
    	if(label_font_style && label_font_style != "normal")
    		font.style = label_font_style;
    	
    	return(font);
    }
    
    /**
     * set general font options
     */
    _setGeneralFontOptions(){
    	
    	const {
    		general_font_size,
    		general_font_weight,
    		general_font_style
    	} = g_objAttributes;
    	
    	var font = {};
    	    	
    	if(general_font_size)
    		Chart.defaults.font.size = general_font_size;
    	
    	if(general_font_weight && general_font_weight != "normal")
    		Chart.defaults.font.weight = general_font_weight;
    	
    	if(general_font_style && general_font_style != "normal")
    		Chart.defaults.font.style = general_font_style;
    	
    	return(font);
    	
    }
    
    //tooltip options
    _getTooltipOptions() {
      const {
        tooltip_enabled,
        tooltip_position,
        tooltip_background_color,
        tooltip_title_color,
        tooltip_title_font_size,
        tooltip_title_font_weight,
        tooltip_title_align,
        tooltip_padding,
        tooltip_corner_radius,
        tooltip_border_color,
        tooltip_border_width,
      } = g_objAttributes;
      if (!tooltip_enabled) {
        return {
          enabled: tooltip_enabled,
        };
      }
      return {
        enabled: tooltip_enabled,
        position: tooltip_position,
        backgroundColor: tooltip_background_color,
        titleColor: tooltip_title_color,
        titleFontSize: tooltip_title_font_size,
        titleFontWeight: tooltip_title_font_weight,
        titleAlign: tooltip_title_align,
        borderColor: tooltip_border_color,
        padding: Number(tooltip_padding),
        cornerRadius: Number(tooltip_corner_radius),
        borderWidth: Number(tooltip_border_width),
      };
    }

    _getInteractionsOptions() {
      const { interactions_intersect, interactions_mode } = g_objAttributes;
      return {
        intersect: interactions_intersect,
        mode: interactions_mode,
      };
    }

    _getAnimationOptions() {
      const {
        animation_enable,
        animation_type,
        animation_from,
        animation_to,
        animation_time,
        animation_easing,
        animation_loop,
      } = g_objAttributes;

      if (!animation_enable) {
        return;
      }

      return {
        [animation_type]: {
          duration: Number(animation_time),
          from: Number(animation_from),
          to: Number(animation_to),
          easing: animation_easing,
          loop: animation_loop,
        },
      };
    }
  }

  const PointChartMixing = (Base) =>
    class extends Base {
      getChartOptions() {
        const {
          radius,
          rotation,
          point_style,
          hover_border_width,
          hover_radius,
          hover_enable,
          border_width,
        } = g_objAttributes;
        const baseOptions = super.getChartOptions();
        const pointHoverOptions = hover_enable
          ? {
              hoverRadius: Number(hover_radius),
              hoverBorderWidth: Number(hover_border_width),
            }
          : {
              hoverRadius: Number(radius),
              hoverBorderWidth: Number(border_width),
            };
        const specificOptions = {
          elements: {
            ...baseOptions.elements,
            point: {
              ...pointHoverOptions,
              pointStyle: point_style,
              radius: Number(radius),
              rotation: Number(rotation),
            },
          },
        };
        const computedOptions = {
          ...baseOptions,
          ...specificOptions,
        };
        return computedOptions;
      }
    };

  const DirectionChartMixing = (Base) =>
    class extends Base {
      getChartOptions() {
        const { direction } = g_objAttributes;
        const baseOptions = super.getChartOptions();
        const specificOptions = {
          indexAxis: direction,
        };
        const computedOptions = {
          ...baseOptions,
          ...specificOptions,
        };
        return computedOptions;
      }
    };

  class BarChart extends DirectionChartMixing(BaseChart) {}

  class LineChart extends DirectionChartMixing(PointChartMixing(BaseChart)) {
    getChartOptions() {
      const { tension } = g_objAttributes;
      const baseOptions = super.getChartOptions();
      const specificOptions = {
        elements: {
          ...baseOptions.elements,
          line: {
            tension: Number(tension) / 10,
          },
        },
      };
      const computedOptions = {
        ...baseOptions,
        ...specificOptions,
      };
      return computedOptions;
    }
    getDatasetOptionsFromSettings() {
      const { border_dash, fill_above, enable_fill_above, fill_below } =
        g_objAttributes;
      const baseOptions = super.getDatasetOptionsFromSettings();
      const specificOptions = {
        borderDash: [border_dash],
        fill: {
          target: "origin",
          below: fill_below,
          above: enable_fill_above ? fill_above : "transparent",
        },
      };
      const computedOptions = {
        ...baseOptions,
        ...specificOptions,
      };
      return computedOptions;
    }
  }

  /**
   * run charts
   */
  function runChart() {
    var ctx = g_objWrapper.find("canvas")[0];
    const {
      chart_type: type,
      show_chart_datasets,
      show_chart_options,
      show_chart_instance,
    } = g_objAttributes;
    
    const chart = new ChartFactory(type);
    
    chart._setGeneralFontOptions();
        
    const data = chart.getChartDatasets();
    const options = chart.getChartOptions();
    const instance = new Chart(ctx, {
      type,
      data: data,
      options: options,
    });
    
    if (show_chart_datasets) {
      console.log("Chart datasets", data);
    }
    if (show_chart_options) {
      console.log("Chart options", options);
    }
    if (show_chart_instance) {
      console.log("Chart instance", instance);
    }
  }

  /**
   * init the charts
   */
  this.init = function (chartID, type, strJsonAttributes, strJsonItems) {
    g_objWrapper = jQuery("#" + chartID);
    if (g_objWrapper.length == 0) {
      console.log("chart with id: " + chartID + " not found");
      return false;
    }

    g_objItems = JSON.parse(strJsonItems);
    g_objAttributes = JSON.parse(strJsonAttributes);

    runChart();
  };
}
