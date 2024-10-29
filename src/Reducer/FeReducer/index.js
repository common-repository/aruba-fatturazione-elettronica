const initialState = {
    ready: false,
    data: ['tax_loaded', 'payment_loaded','banks_loaded']
};

const FeReducer = (state, action) => {
    switch (action.type) {
        case 'add':
            const newDataAdd = [...state.data, action.func];
            return { ...state, data: newDataAdd, ready: newDataAdd.length === 0 };
        case 'remove':
            const newDataRemove = state.data.filter(item => item !== action.func);
            return { ...state, data: newDataRemove, ready: newDataRemove.length === 0 };
        default:
            return state;
    }
};

export { initialState, FeReducer }