/* App JS Routes */

import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

const routes = require('../../../public/js/fos_js_routes.json');

Routing.setRoutingData(routes);

export const bgpProtocolDetailApi = () => Routing.generate('bgp_protocol_detail_api');

export const routeDetailApi = () => Routing.generate('route_detail_api');

export const communityLookup = (server, table) => Routing.generate('community_lookup', {
    server: server,
    table: table
});

export const importedRoutes = (server, protocol) => Routing.generate('imported_routes', {
    server: server,
    protocol: protocol
});

export const exportedRoutes = (server, protocol) => Routing.generate('exported_routes', {
    server: server,
    protocol: protocol
});

export const tableRoutes = (server, table) => Routing.generate('table_routes', {
    server: server,
    table: table
});

export const tableInvalidRoutes = (server, table) => Routing.generate('table_invalid_routes', {
    server: server,
    table: table
});

export const tableFilteredRoutes = (server, table) => Routing.generate('table_filtered_routes', {
    server: server,
    table: table
});

export { Routing };
