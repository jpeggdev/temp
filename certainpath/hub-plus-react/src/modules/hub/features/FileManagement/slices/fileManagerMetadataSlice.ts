// Updated fileManagerMetadataSlice.ts
import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { AppDispatch, AppThunkGeneric } from "@/app/store";
import { getFileManagerMetaData } from "../api/getFileManagerMetaData/getFileManagerMetaDataApi";
import {
  TagStatDTO,
  FileTypeStatDTO,
} from "../api/getFileManagerMetaData/types";
import { CreateAndAssignTagData } from "../api/createAndAssignTag/types";

interface FileManagerMetadataState {
  loading: boolean;
  error: string | null;
  tags: TagStatDTO[];
  fileTypes: FileTypeStatDTO[];
}

const initialState: FileManagerMetadataState = {
  loading: false,
  error: null,
  tags: [],
  fileTypes: [],
};

export const fileManagerMetadataSlice = createSlice({
  name: "fileManagerMetadata",
  initialState,
  reducers: {
    setLoading(state, action: PayloadAction<boolean>) {
      state.loading = action.payload;
    },
    setError(state, action: PayloadAction<string | null>) {
      state.error = action.payload;
    },
    setTags(state, action: PayloadAction<TagStatDTO[]>) {
      state.tags = action.payload;
    },
    setFileTypes(state, action: PayloadAction<FileTypeStatDTO[]>) {
      state.fileTypes = action.payload;
    },
    // Tag count operations
    incrementTagCount(state, action: PayloadAction<number>) {
      const tagId = action.payload;
      const tagIndex = state.tags.findIndex((tag) => tag.id === tagId);
      if (tagIndex !== -1) {
        state.tags[tagIndex].count += 1;
      }
    },
    decrementTagCount(state, action: PayloadAction<number>) {
      const tagId = action.payload;
      const tagIndex = state.tags.findIndex((tag) => tag.id === tagId);
      if (tagIndex !== -1 && state.tags[tagIndex].count > 0) {
        state.tags[tagIndex].count -= 1;
      }
    },
    // File type count operations
    incrementFileTypeCount(state, action: PayloadAction<string>) {
      const fileType = action.payload;
      const fileTypeIndex = state.fileTypes.findIndex(
        (type) => type.type === fileType,
      );
      if (fileTypeIndex !== -1) {
        state.fileTypes[fileTypeIndex].count += 1;
      }
    },
    decrementFileTypeCount(state, action: PayloadAction<string>) {
      const fileType = action.payload;
      const fileTypeIndex = state.fileTypes.findIndex(
        (type) => type.type === fileType,
      );
      if (fileTypeIndex !== -1 && state.fileTypes[fileTypeIndex].count > 0) {
        state.fileTypes[fileTypeIndex].count -= 1;
      }
    },
    // Add a newly created tag to metadata
    addNewTag(state, action: PayloadAction<CreateAndAssignTagData>) {
      const newTagData = action.payload;

      // Check if tag already exists (shouldn't happen, but just in case)
      const exists = state.tags.some((tag) => tag.id === newTagData.id);

      if (!exists) {
        // Create new TagStatDTO from the response data
        const newTag: TagStatDTO = {
          id: newTagData.id,
          name: newTagData.name,
          color: newTagData.color,
          count: 1, // Start with count of 1 since it's being assigned to one file
        };

        // Add to the tags array
        state.tags.push(newTag);
      } else {
        // If it somehow exists, increment its count
        const tagIndex = state.tags.findIndex(
          (tag) => tag.id === newTagData.id,
        );
        state.tags[tagIndex].count += 1;
      }
    },
    // For bulk operations, update multiple tag counts at once
    updateMultipleTagCounts(
      state,
      action: PayloadAction<{ tagIds: number[]; increment: boolean }>,
    ) {
      const { tagIds, increment } = action.payload;

      tagIds.forEach((tagId) => {
        const tagIndex = state.tags.findIndex((tag) => tag.id === tagId);
        if (tagIndex !== -1) {
          if (increment) {
            state.tags[tagIndex].count += 1;
          } else if (state.tags[tagIndex].count > 0) {
            state.tags[tagIndex].count -= 1;
          }
        }
      });
    },
    // For bulk operations, update multiple file type counts at once
    updateMultipleFileTypeCounts(
      state,
      action: PayloadAction<{ fileTypes: string[]; increment: boolean }>,
    ) {
      const { fileTypes, increment } = action.payload;

      fileTypes.forEach((fileType) => {
        const fileTypeIndex = state.fileTypes.findIndex(
          (type) => type.type === fileType,
        );
        if (fileTypeIndex !== -1) {
          if (increment) {
            state.fileTypes[fileTypeIndex].count += 1;
          } else if (state.fileTypes[fileTypeIndex].count > 0) {
            state.fileTypes[fileTypeIndex].count -= 1;
          }
        }
      });
    },
  },
});

export const {
  setLoading,
  setError,
  setTags,
  setFileTypes,
  incrementTagCount,
  decrementTagCount,
  incrementFileTypeCount,
  decrementFileTypeCount,
  addNewTag,
  updateMultipleTagCounts,
  updateMultipleFileTypeCounts,
} = fileManagerMetadataSlice.actions;

export const fetchFileManagerMetadata =
  (): AppThunkGeneric<Promise<void>> => async (dispatch: AppDispatch) => {
    try {
      dispatch(setLoading(true));
      dispatch(setError(null));

      const response = await getFileManagerMetaData();

      dispatch(setTags(response.data.tags));
      dispatch(setFileTypes(response.data.fileTypes));

      return Promise.resolve();
    } catch (error) {
      const errorMessage =
        error instanceof Error
          ? error.message
          : "Failed to load file manager metadata";
      dispatch(setError(errorMessage));
      return Promise.reject(error);
    } finally {
      dispatch(setLoading(false));
    }
  };

export default fileManagerMetadataSlice.reducer;
