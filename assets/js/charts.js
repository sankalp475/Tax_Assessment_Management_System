/**
 * Tax Assessment Management System - Charts JavaScript File
 *
 * This file contains functions for creating various charts using D3.js
 */

/**
 * Initialize the Monthly Returns Chart
 * @param {Array} data - The monthly returns data
 */
function initMonthlyReturnsChart(data) {
    // Clear existing chart if any
    d3.select("#monthly-returns-chart").html("");

    // Set dimensions and margins
    const margin = { top: 30, right: 30, bottom: 70, left: 60 };
    const width = document.getElementById('monthly-returns-chart').clientWidth - margin.left - margin.right;
    const height = 300 - margin.top - margin.bottom;

    // Create SVG element
    const svg = d3.select("#monthly-returns-chart")
        .append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", `translate(${margin.left},${margin.top})`);

    // X axis
    const x = d3.scaleBand()
        .range([0, width])
        .domain(data.map(d => d.month))
        .padding(0.2);
    
    svg.append("g")
        .attr("transform", `translate(0,${height})`)
        .call(d3.axisBottom(x))
        .selectAll("text")
        .attr("transform", "translate(-10,0)rotate(-45)")
        .style("text-anchor", "end");

    // Add Y axis
    const y = d3.scaleLinear()
        .domain([0, d3.max(data, d => d.count) * 1.2])
        .range([height, 0]);
    
    svg.append("g")
        .call(d3.axisLeft(y));

    // Add bars
    svg.selectAll("rect")
        .data(data)
        .join("rect")
        .attr("x", d => x(d.month))
        .attr("y", d => y(0))
        .attr("width", x.bandwidth())
        .attr("height", 0)
        .attr("fill", "#4e73df")
        .transition()
        .duration(800)
        .attr("y", d => y(d.count))
        .attr("height", d => height - y(d.count));

    // Add labels
    svg.selectAll(".label")
        .data(data)
        .join("text")
        .attr("class", "label")
        .attr("text-anchor", "middle")
        .attr("x", d => x(d.month) + x.bandwidth() / 2)
        .attr("y", d => y(d.count) - 5)
        .text(d => d.count)
        .style("opacity", 0)
        .transition()
        .duration(800)
        .style("opacity", 1);

    // Add title
    svg.append("text")
        .attr("x", width / 2)
        .attr("y", 0 - (margin.top / 2))
        .attr("text-anchor", "middle")
        .style("font-size", "16px")
        .style("font-weight", "bold")
        .text("Monthly Returns Filed");
}

/**
 * Initialize the Return Types Chart
 * @param {Array} data - The return types data
 */
function initReturnTypesChart(data) {
    // Clear existing chart if any
    d3.select("#return-types-chart").html("");

    // Set dimensions and margins
    const width = document.getElementById('return-types-chart').clientWidth;
    const height = 300;
    const margin = 40;
    const radius = Math.min(width, height) / 2 - margin;

    // Create SVG element
    const svg = d3.select("#return-types-chart")
        .append("svg")
        .attr("width", width)
        .attr("height", height)
        .append("g")
        .attr("transform", `translate(${width / 2},${height / 2})`);

    // Set colors
    const color = d3.scaleOrdinal()
        .domain(data.map(d => d.type))
        .range(d3.schemeCategory10);

    // Compute position of each group on the pie
    const pie = d3.pie()
        .value(d => d.count);

    const data_ready = pie(data);

    // Build the pie chart
    svg.selectAll('path')
        .data(data_ready)
        .join('path')
        .attr('d', d3.arc()
            .innerRadius(0)
            .outerRadius(0)
        )
        .attr('fill', d => color(d.data.type))
        .attr("stroke", "white")
        .style("stroke-width", "2px")
        .transition()
        .duration(1000)
        .attr('d', d3.arc()
            .innerRadius(0)
            .outerRadius(radius)
        );

    // Add labels
    svg.selectAll('text')
        .data(data_ready)
        .join('text')
        .text(d => `${d.data.type}: ${d.data.count}`)
        .attr("transform", d => {
            const pos = d3.arc()
                .innerRadius(radius / 2)
                .outerRadius(radius)
                .centroid(d);
            return `translate(${pos})`;
        })
        .style("text-anchor", "middle")
        .style("font-size", "12px")
        .style("fill", "white")
        .style("font-weight", "bold")
        .style("opacity", 0)
        .transition()
        .delay(1000)
        .duration(500)
        .style("opacity", 1);
}

/**
 * Initialize Financial Data Chart
 * @param {Array} data - The financial data
 * @param {string} elementId - The ID of the element to render the chart
 * @param {string} title - The chart title
 * @param {Array} fields - The fields to include in the chart
 */
function initFinancialDataChart(data, elementId, title, fields) {
    // Clear existing chart if any
    d3.select(`#${elementId}`).html("");

    // Set dimensions and margins
    const margin = { top: 40, right: 80, bottom: 60, left: 80 };
    const width = document.getElementById(elementId).clientWidth - margin.left - margin.right;
    const height = 400 - margin.top - margin.bottom;

    // Create SVG element
    const svg = d3.select(`#${elementId}`)
        .append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", `translate(${margin.left},${margin.top})`);

    // X axis
    const x = d3.scaleBand()
        .range([0, width])
        .domain(data.map(d => d.year))
        .padding(0.2);
    
    svg.append("g")
        .attr("transform", `translate(0,${height})`)
        .call(d3.axisBottom(x))
        .selectAll("text")
        .attr("transform", "translate(-10,0)rotate(-45)")
        .style("text-anchor", "end");

    // Add X axis label
    svg.append("text")
        .attr("transform", `translate(${width / 2}, ${height + margin.bottom - 10})`)
        .style("text-anchor", "middle")
        .text("Financial Year");

    // Find max value for Y axis
    const maxValue = d3.max(data, d => {
        return d3.max(fields.map(field => +d[field]));
    });

    // Y axis
    const y = d3.scaleLinear()
        .domain([0, maxValue * 1.1])
        .range([height, 0]);
    
    svg.append("g")
        .call(d3.axisLeft(y));

    // Add Y axis label
    svg.append("text")
        .attr("transform", "rotate(-90)")
        .attr("y", 0 - margin.left)
        .attr("x", 0 - (height / 2))
        .attr("dy", "1em")
        .style("text-anchor", "middle")
        .text("Amount (₹)");

    // Add colors
    const color = d3.scaleOrdinal()
        .domain(fields)
        .range(d3.schemeCategory10);

    // Create line generators
    fields.forEach(field => {
        const line = d3.line()
            .x(d => x(d.year) + x.bandwidth() / 2)
            .y(d => y(d[field]));

        // Add the line
        svg.append("path")
            .datum(data)
            .attr("fill", "none")
            .attr("stroke", color(field))
            .attr("stroke-width", 3)
            .attr("d", line)
            .style("opacity", 0)
            .transition()
            .duration(1000)
            .style("opacity", 1);

        // Add the points
        svg.selectAll(`dot-${field}`)
            .data(data)
            .join("circle")
            .attr("cx", d => x(d.year) + x.bandwidth() / 2)
            .attr("cy", d => y(d[field]))
            .attr("r", 0)
            .attr("fill", color(field))
            .transition()
            .duration(1000)
            .attr("r", 5);
    });

    // Add legend
    const legend = svg.append("g")
        .attr("font-family", "sans-serif")
        .attr("font-size", 10)
        .attr("text-anchor", "end")
        .selectAll("g")
        .data(fields)
        .join("g")
        .attr("transform", (d, i) => `translate(0,${i * 20})`);

    legend.append("rect")
        .attr("x", width + 20)
        .attr("width", 19)
        .attr("height", 19)
        .attr("fill", color);

    legend.append("text")
        .attr("x", width + 15)
        .attr("y", 9.5)
        .attr("dy", "0.32em")
        .text(d => d);

    // Add title
    svg.append("text")
        .attr("x", width / 2)
        .attr("y", 0 - (margin.top / 2))
        .attr("text-anchor", "middle")
        .style("font-size", "16px")
        .style("font-weight", "bold")
        .text(title);
}

/**
 * Initialize Balance Sheet Composition Chart
 * @param {Object} data - The balance sheet data
 * @param {string} elementId - The ID of the element to render the chart
 */
function initBalanceSheetCompositionChart(data, elementId) {
    // Clear existing chart if any
    d3.select(`#${elementId}`).html("");

    // Prepare data for stacked bar chart
    const categories = ["Assets", "Liabilities"];
    const assetsPrepared = [];
    const liabilitiesPrepared = [];

    // Process assets
    for (const key in data.assets) {
        if (data.assets[key] > 0) {
            assetsPrepared.push({
                category: "Assets",
                name: key,
                value: data.assets[key]
            });
        }
    }

    // Process liabilities
    for (const key in data.liabilities) {
        if (data.liabilities[key] > 0) {
            liabilitiesPrepared.push({
                category: "Liabilities",
                name: key,
                value: data.liabilities[key]
            });
        }
    }

    const combinedData = [...assetsPrepared, ...liabilitiesPrepared];

    // Set dimensions and margins
    const margin = { top: 40, right: 120, bottom: 60, left: 120 };
    const width = document.getElementById(elementId).clientWidth - margin.left - margin.right;
    const height = 400 - margin.top - margin.bottom;

    // Create SVG element
    const svg = d3.select(`#${elementId}`)
        .append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", `translate(${margin.left},${margin.top})`);

    // X axis
    const x = d3.scaleBand()
        .range([0, width])
        .domain(categories)
        .padding(0.3);
    
    svg.append("g")
        .attr("transform", `translate(0,${height})`)
        .call(d3.axisBottom(x));

    // Y axis
    const maxValue = d3.max(categories, category => {
        return d3.sum(combinedData.filter(d => d.category === category), d => d.value);
    });

    const y = d3.scaleLinear()
        .domain([0, maxValue * 1.1])
        .range([height, 0]);
    
    svg.append("g")
        .call(d3.axisLeft(y));

    // Color scale
    const color = d3.scaleOrdinal()
        .domain(combinedData.map(d => d.name))
        .range(d3.schemeCategory10);

    // Stack the data
    const stackedData = {};
    categories.forEach(category => {
        stackedData[category] = [];
        let cumulativeSum = 0;
        
        combinedData.filter(d => d.category === category).forEach(d => {
            stackedData[category].push({
                name: d.name,
                value: d.value,
                y0: cumulativeSum,
                y1: cumulativeSum + d.value
            });
            cumulativeSum += d.value;
        });
    });

    // Add bars
    categories.forEach(category => {
        svg.selectAll(`.bar-${category}`)
            .data(stackedData[category])
            .join("rect")
            .attr("class", `bar-${category}`)
            .attr("x", x(category))
            .attr("width", x.bandwidth())
            .attr("y", d => y(d.y1))
            .attr("height", d => y(d.y0) - y(d.y1))
            .attr("fill", d => color(d.name))
            .on("mouseover", function(event, d) {
                // Show tooltip
                d3.select(this).attr("opacity", 0.8);
                
                svg.append("text")
                    .attr("class", "tooltip")
                    .attr("x", x(category) + x.bandwidth() / 2)
                    .attr("y", y(d.y1) - 5)
                    .attr("text-anchor", "middle")
                    .text(`${d.name}: ₹${d.value.toLocaleString()}`);
            })
            .on("mouseout", function() {
                // Hide tooltip
                d3.select(this).attr("opacity", 1);
                svg.selectAll(".tooltip").remove();
            });
    });

    // Add labels
    categories.forEach(category => {
        svg.selectAll(`.label-${category}`)
            .data(stackedData[category])
            .join("text")
            .attr("class", `label-${category}`)
            .attr("x", x(category) + x.bandwidth() / 2)
            .attr("y", d => {
                const height = y(d.y0) - y(d.y1);
                return height > 20 ? (y(d.y1) + (y(d.y0) - y(d.y1)) / 2) : (y(d.y1) - 5);
            })
            .attr("text-anchor", "middle")
            .attr("dominant-baseline", "middle")
            .text(d => {
                const height = y(d.y0) - y(d.y1);
                return height > 40 ? `${d.name}` : "";
            })
            .style("fill", d => {
                const height = y(d.y0) - y(d.y1);
                return height > 20 ? "white" : "black";
            })
            .style("font-size", "12px");
    });

    // Add title
    svg.append("text")
        .attr("x", width / 2)
        .attr("y", 0 - (margin.top / 2))
        .attr("text-anchor", "middle")
        .style("font-size", "16px")
        .style("font-weight", "bold")
        .text("Balance Sheet Composition");

    // Add legend
    const legendNames = combinedData.map(d => d.name).filter((v, i, a) => a.indexOf(v) === i);
    
    const legend = svg.append("g")
        .attr("font-family", "sans-serif")
        .attr("font-size", 10)
        .attr("text-anchor", "end")
        .selectAll("g")
        .data(legendNames)
        .join("g")
        .attr("transform", (d, i) => `translate(0,${i * 20})`);

    legend.append("rect")
        .attr("x", width + 20)
        .attr("width", 19)
        .attr("height", 19)
        .attr("fill", color);

    legend.append("text")
        .attr("x", width + 15)
        .attr("y", 9.5)
        .attr("dy", "0.32em")
        .text(d => d);
}

/**
 * Initialize Client Distribution Chart
 * @param {Array} data - The client distribution data
 * @param {string} elementId - The ID of the element to render the chart
 */
function initClientDistributionChart(data, elementId) {
    // Clear existing chart if any
    d3.select(`#${elementId}`).html("");

    // Set dimensions and margins
    const width = document.getElementById(elementId).clientWidth;
    const height = 300;
    const margin = { top: 30, right: 30, bottom: 70, left: 60 };
    const innerWidth = width - margin.left - margin.right;
    const innerHeight = height - margin.top - margin.bottom;

    // Create SVG element
    const svg = d3.select(`#${elementId}`)
        .append("svg")
        .attr("width", width)
        .attr("height", height)
        .append("g")
        .attr("transform", `translate(${margin.left},${margin.top})`);

    // X axis
    const x = d3.scaleBand()
        .range([0, innerWidth])
        .domain(data.map(d => d.type))
        .padding(0.2);
    
    svg.append("g")
        .attr("transform", `translate(0,${innerHeight})`)
        .call(d3.axisBottom(x));

    // Y axis
    const y = d3.scaleLinear()
        .domain([0, d3.max(data, d => d.count) * 1.1])
        .range([innerHeight, 0]);
    
    svg.append("g")
        .call(d3.axisLeft(y));

    // Color scale
    const color = d3.scaleOrdinal()
        .domain(data.map(d => d.type))
        .range(['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e']);

    // Add bars
    svg.selectAll("rect")
        .data(data)
        .join("rect")
        .attr("x", d => x(d.type))
        .attr("y", innerHeight)
        .attr("width", x.bandwidth())
        .attr("height", 0)
        .attr("fill", d => color(d.type))
        .transition()
        .duration(800)
        .attr("y", d => y(d.count))
        .attr("height", d => innerHeight - y(d.count));

    // Add labels
    svg.selectAll(".label")
        .data(data)
        .join("text")
        .attr("class", "label")
        .attr("text-anchor", "middle")
        .attr("x", d => x(d.type) + x.bandwidth() / 2)
        .attr("y", d => y(d.count) - 5)
        .text(d => d.count)
        .style("font-size", "12px")
        .style("opacity", 0)
        .transition()
        .duration(800)
        .style("opacity", 1);

    // Add title
    svg.append("text")
        .attr("x", innerWidth / 2)
        .attr("y", 0 - (margin.top / 2))
        .attr("text-anchor", "middle")
        .style("font-size", "16px")
        .style("font-weight", "bold")
        .text("Client Distribution by Type");
}

/**
 * Initialize Return Comparison Chart
 * @param {Array} data - The return comparison data
 * @param {string} elementId - The ID of the element to render the chart
 */
function initReturnComparisonChart(data, elementId) {
    // Clear existing chart if any
    d3.select(`#${elementId}`).html("");

    // Set dimensions and margins
    const margin = { top: 50, right: 80, bottom: 70, left: 80 };
    const width = document.getElementById(elementId).clientWidth - margin.left - margin.right;
    const height = 400 - margin.top - margin.bottom;

    // Create SVG element
    const svg = d3.select(`#${elementId}`)
        .append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", `translate(${margin.left},${margin.top})`);

    // X axis
    const x = d3.scaleBand()
        .range([0, width])
        .domain(data.map(d => d.year))
        .padding(0.2);
    
    svg.append("g")
        .attr("transform", `translate(0,${height})`)
        .call(d3.axisBottom(x))
        .selectAll("text")
        .attr("transform", "translate(-10,0)rotate(-45)")
        .style("text-anchor", "end");

    // Add X axis label
    svg.append("text")
        .attr("transform", `translate(${width / 2}, ${height + margin.bottom - 10})`)
        .style("text-anchor", "middle")
        .text("Assessment Year");

    // Y axis
    const y = d3.scaleLinear()
        .domain([0, d3.max(data, d => Math.max(d.original, d.revised)) * 1.1])
        .range([height, 0]);
    
    svg.append("g")
        .call(d3.axisLeft(y));

    // Add Y axis label
    svg.append("text")
        .attr("transform", "rotate(-90)")
        .attr("y", 0 - margin.left)
        .attr("x", 0 - (height / 2))
        .attr("dy", "1em")
        .style("text-anchor", "middle")
        .text("Number of Returns");

    // Add bars for original returns
    svg.selectAll(".bar-original")
        .data(data)
        .join("rect")
        .attr("class", "bar-original")
        .attr("x", d => x(d.year))
        .attr("width", x.bandwidth() / 2)
        .attr("y", d => y(d.original))
        .attr("height", d => height - y(d.original))
        .attr("fill", "#4e73df")
        .attr("opacity", 0)
        .transition()
        .duration(800)
        .attr("opacity", 1);

    // Add bars for revised returns
    svg.selectAll(".bar-revised")
        .data(data)
        .join("rect")
        .attr("class", "bar-revised")
        .attr("x", d => x(d.year) + x.bandwidth() / 2)
        .attr("width", x.bandwidth() / 2)
        .attr("y", d => y(d.revised))
        .attr("height", d => height - y(d.revised))
        .attr("fill", "#1cc88a")
        .attr("opacity", 0)
        .transition()
        .duration(800)
        .attr("opacity", 1);

    // Add labels for original returns
    svg.selectAll(".label-original")
        .data(data)
        .join("text")
        .attr("class", "label-original")
        .attr("text-anchor", "middle")
        .attr("x", d => x(d.year) + x.bandwidth() / 4)
        .attr("y", d => y(d.original) - 5)
        .text(d => d.original)
        .style("font-size", "10px")
        .style("opacity", 0)
        .transition()
        .duration(800)
        .style("opacity", 1);

    // Add labels for revised returns
    svg.selectAll(".label-revised")
        .data(data)
        .join("text")
        .attr("class", "label-revised")
        .attr("text-anchor", "middle")
        .attr("x", d => x(d.year) + x.bandwidth() * 3 / 4)
        .attr("y", d => y(d.revised) - 5)
        .text(d => d.revised)
        .style("font-size", "10px")
        .style("opacity", 0)
        .transition()
        .duration(800)
        .style("opacity", 1);

    // Add title
    svg.append("text")
        .attr("x", width / 2)
        .attr("y", 0 - (margin.top / 2))
        .attr("text-anchor", "middle")
        .style("font-size", "16px")
        .style("font-weight", "bold")
        .text("Original vs Revised Returns by Year");

    // Add legend
    const legendData = [
        { label: "Original Returns", color: "#4e73df" },
        { label: "Revised Returns", color: "#1cc88a" }
    ];

    const legend = svg.append("g")
        .attr("font-family", "sans-serif")
        .attr("font-size", 10)
        .attr("text-anchor", "end")
        .selectAll("g")
        .data(legendData)
        .join("g")
        .attr("transform", (d, i) => `translate(0,${i * 20})`);

    legend.append("rect")
        .attr("x", width - 19)
        .attr("width", 19)
        .attr("height", 19)
        .attr("fill", d => d.color);

    legend.append("text")
        .attr("x", width - 24)
        .attr("y", 9.5)
        .attr("dy", "0.32em")
        .text(d => d.label);
}
