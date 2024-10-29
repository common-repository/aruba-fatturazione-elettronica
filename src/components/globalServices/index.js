import apiFetch from '@wordpress/api-fetch';
const GlobalServices = {

	updateData: (arubaData) => {
		return new Promise((resolve, reject) => {
			apiFetch({ path: '/aruba_fe/v1/update_global_data', method: 'POST', data: { aruba_global_data: arubaData, nonce: aruba_fe_data.nonce } }).then((data) => {
				resolve({
					data,
					message: "Update in corso..."
				})
			}).catch((error) => {
				reject(error)
			});

		})
	},

	getData: () => {

		return new Promise((resolve, reject) => {
			apiFetch({ path: '/aruba_fe/v1/get_global_data', method: 'POST', data: { nonce: aruba_fe_data.nonce } }).then((data) => {
				resolve({
					data,
					message: "Recupero dati in corso..."
				})
			});

		}).catch((error) => {
			reject(error)
		})
	}
}

export default GlobalServices;
