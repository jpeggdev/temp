import { createSlice, PayloadAction } from "@reduxjs/toolkit";

interface WideSidebarState {
  openItems: Record<string, boolean>;
}

const initialState: WideSidebarState = {
  openItems: {},
};

export const wideSidebarSlice = createSlice({
  name: "wideSidebar",
  initialState,
  reducers: {
    toggleWideSidebarItem: (state, action: PayloadAction<string>) => {
      const itemKey = action.payload;
      if (state.openItems[itemKey]) {
        delete state.openItems[itemKey];
      } else {
        state.openItems[itemKey] = true;
      }
    },
  },
});

export const { toggleWideSidebarItem } = wideSidebarSlice.actions;

export default wideSidebarSlice.reducer;
