import { configureStore, Action, ThunkAction } from "@reduxjs/toolkit";
import rootReducer, { RootState } from "./rootReducer";

export type AppThunk = ThunkAction<void, RootState, undefined, Action<string>>;
export type AppThunkGeneric<R> = ThunkAction<
  R,
  RootState,
  undefined,
  Action<string>
>;

const store = configureStore({
  reducer: rootReducer,
});

export type AppDispatch = typeof store.dispatch;

export default store;
