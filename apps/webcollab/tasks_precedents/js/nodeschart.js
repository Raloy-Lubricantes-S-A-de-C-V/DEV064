$(document).ready(function () {
    createChart();
});


function createChart() {
    let url = "ajax/nodeschart.php";
    let param = {
        "fx": "createChart",
        "projectid": $("#projectid").val()
    };
    $.get(url, param, function (response) {
        if (response.status === 1) {
            var i,
                    s,
                    N = 100,
                    E = 500;
                    
            var graph = response.graph;

            var s = new sigma(
                    {
                        renderer: {
                            container: document.getElementById('container'),
                            type: 'canvas'
                        },
                        settings: {
                            edgeLabelSize: 'proportional',
                            minArrowSize: 10
                        }
                    }
            );
            console.log(graph);
            s.graph.read(graph);
            // draw the graph
            s.refresh();

        } else {
            console.log(response.error);
        }
    }, "json");

    /**
     * This is a basic example on how to instantiate sigma. A random graph is
     * generated and stored in the "graph" variable, and then sigma is instantiated
     * directly with the graph.
     *
     * The simple instance of sigma is enough "to make it render the graph on the on
     * the screen, since the graph is given directly to the constructor.
     */

//    ,graph = {
//                nodes: [
//                    {
//                        id: '1',
//                        label: 'Suministro eléctrico',
//                        x: 10,
//                        y: 10,
//                        size: 35,
//                        color: '#a8dff7'
//                    },
//                    {
//                        id: '2',
//                        label: 'Instalación eléctrica',
//                        x: 10,
//                        y: 15,
//                        size: 35,
//                        color: '#a8dff7'
//                    },
//                    {
//                        id: '3',
//                        label: 'RO',
//                        x: 15,
//                        y: 12,
//                        size: 35,
//                        color: '#a8dff7'
//                    },
//                    {
//                        id: '4',
//                        label: 'T. EPT',
//                        x: 10,
//                        y: 17,
//                        size: 35,
//                        color: '#a8dff7'
//                    },
//                    {
//                        id: '5',
//                        label: 'Instalación Bombas',
//                        x: 10,
//                        y: 20,
//                        size: 35,
//                        color: '#a8dff7'
//                    },
//                    {
//                        id: '6',
//                        label: 'Arranque',
//                        x: 20,
//                        y: 20,
//                        size: 35,
//                        color: '#a8dff7'
//                    }
//                ],
//                edges: [
//                    {
//                        id: '1-3',
//                        source: '1',
//                        target: '3',
//                        size: 10,
//                        color: '#55ed2e',
//                        type: 'arrow'
//                    },
//                    {
//                        id: '2-3',
//                        source: '2',
//                        target: '3',
//                        size: 0.5,
//                        color: '#a7a7a7',
//                        type: 'arrow'
//                    },
//                    {
//                        id: '3-6',
//                        source: '3',
//                        target: '6',
//                        size: 0.5,
//                        color: '#a7a7a7',
//                        type: 'line'
//                    },
//                    {
//                        id: '4-6',
//                        source: '4',
//                        target: '6',
//                        size: 0.5,
//                        color: '#a7a7a7',
//                        type: 'curve'
//                    },
//                    {
//                        id: '5-6',
//                        source: '5',
//                        target: '6',
//                        size: 0.5,
//                        color: '#a7a7a7',
//                        type: 'dotted'
//                    }
//                ]
//            };

// Generate a random graph:
//    for (i = 0; i < N; i++)
//        g.nodes.push({
//            id: 'n' + i,
//            label: 'Node ' + i,
//            x: Math.random(),
//            y: Math.random(),
//            size: Math.random(),
//            color: '#666'
//        });

//    for (i = 0; i < E; i++)
//        g.edges.push({
//            id: 'e' + i,
//            source: 'n' + (Math.random() * N | 0),
//            target: 'n' + (Math.random() * N | 0),
//            size: Math.random(),
//            color: '#ccc',
//            type: [
//                'line',
//                'curve',
//                'arrow',
//                'curvedArrow',
//                'dashed',
//                'dotted',
//                'parallel',
//                'tapered'
//            ][Math.round(Math.random() * 8)]
//        });

// Instantiate sigma:



//// Generate a random graph:
//    var nbNode = 50;
//    var nbEdge = 100;
//    var graph = {
//        nodes: [],
//        edges: []
//    };
//    for (i = 0; i < nbNode; i++)
//        graph.nodes.push({
//            id: i,
//            label: 'Node ' + i,
//            x: Math.random(),
//            y: Math.random(),
//            size: 1,
//            color: '#EE651D'
//        });
//
//    for (i = 0; i < nbEdge; i++)
//        graph.edges.push({
//            id: i,
//            label: 'Edge ' + i,
//            source: '' + (Math.random() * nbNode | 0),
//            target: '' + (Math.random() * nbNode | 0),
//            color: '#00000',
//            type: 'curvedArrow',
//        });


// load the graph

// launch force-atlas for 5sec
//    s.startForceAtlas2();
//    window.setTimeout(function () {
//        s.killForceAtlas2()
//    }, 10000);
}