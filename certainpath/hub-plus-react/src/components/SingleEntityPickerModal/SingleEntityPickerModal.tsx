import React, { useState, useEffect, ReactNode } from "react";
import Modal from "react-modal";
import InfiniteScroll from "react-infinite-scroll-component";
import { Button } from "@/components/Button/Button";
import { useDebouncedValue } from "@/hooks/useDebouncedValue";
import LoadingIndicator from "@/components/LoadingIndicator/LoadingIndicator";
import CreateEntityDrawer from "@/components/CreateEntityDrawer/CreateEntityDrawer";
import { CheckIcon } from "@heroicons/react/24/outline";

export type BaseEntity = {
  id: string | number;
  name: string;
  color?: Color | null;
};

export interface Color {
  id: number;
  value: string;
}

export type FetchEntitiesFn<T extends BaseEntity> = (params: {
  searchTerm?: string;
  page?: number;
  pageSize?: number;
}) => Promise<{
  data: T[];
  totalCount: number;
}>;

export type CreateEntityFn<T extends BaseEntity> = (params: {
  name: string;
}) => Promise<T>;

export interface EntityPickerModalProps<T extends BaseEntity> {
  isOpen: boolean;
  onClose: () => void;
  fetchEntities: FetchEntitiesFn<T>;
  createEntity?: CreateEntityFn<T>;
  initialSelectedEntity: T | null;
  onConfirm: (selectedEntity: T | null) => void;
  entityNameSingular?: string;
  entityNamePlural?: string;
  renderEntityRow?: (
    entity: T,
    isSelected: boolean,
    toggle: (ent: T) => void,
  ) => ReactNode;
}

export default function SingleEntityPickerModal<T extends BaseEntity>({
  isOpen,
  onClose,
  fetchEntities,
  createEntity,
  initialSelectedEntity,
  onConfirm,
  entityNameSingular = "Entity",
  entityNamePlural = "Entities",
  renderEntityRow,
}: EntityPickerModalProps<T>) {
  const [searchTerm, setSearchTerm] = useState("");
  const [entities, setEntities] = useState<T[]>([]);
  const [selectedEntity, setSelectedEntity] = useState<T | null>(
    initialSelectedEntity,
  );
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [isLoadingInitial, setIsLoadingInitial] = useState(false);
  const [showCreateDrawer, setShowCreateDrawer] = useState(false);
  const debouncedSearchTerm = useDebouncedValue(searchTerm, 500);

  const headingText = `Please Select ${entityNameSingular}`;

  useEffect(() => {
    if (isOpen) {
      setSelectedEntity(initialSelectedEntity);
      setSearchTerm("");
      setEntities([]);
      setPage(1);
      setHasMore(true);
      setIsLoadingInitial(false);
      setShowCreateDrawer(false);
    }
  }, [isOpen, initialSelectedEntity]);

  useEffect(() => {
    if (!isOpen) return;
    setEntities([]);
    setPage(1);
    setHasMore(true);
    loadEntities(1, debouncedSearchTerm);
  }, [isOpen, debouncedSearchTerm]);

  const loadEntities = (pageNumber: number, sTerm: string) => {
    if (pageNumber === 1) {
      setIsLoadingInitial(true);
    }
    fetchEntities({
      searchTerm: sTerm,
      page: pageNumber,
      pageSize: 10,
    })
      .then((res) => {
        const newData = res.data;
        setEntities((prev) =>
          pageNumber === 1 ? newData : [...prev, ...newData],
        );
        if (newData.length < 10) {
          setHasMore(false);
        }
      })
      .catch(() => {
        setHasMore(false);
      })
      .finally(() => {
        setIsLoadingInitial(false);
      });
  };

  const fetchNext = () => {
    const nextPage = page + 1;
    setPage(nextPage);
    loadEntities(nextPage, debouncedSearchTerm);
  };

  const toggleSelection = (ent: T) => {
    setSelectedEntity((prev) => {
      if (prev?.id === ent.id) {
        return null;
      }
      return ent;
    });
  };

  const handleEntityCreated = (newEntity: T) => {
    setEntities((prev) => [newEntity, ...prev]);
    setSelectedEntity(newEntity);
  };

  const defaultRenderRow = (ent: T, isSelected: boolean) => (
    <div
      className={`
        cursor-pointer flex items-center gap-4 py-4 px-2 border-b last:border-0
        ${isSelected ? "bg-blue-50" : ""}
      `}
      key={ent.id}
      onClick={() => toggleSelection(ent)}
    >
      <div className="flex-1">
        <p className="font-medium">{ent.name}</p>
      </div>
      {isSelected && (
        <CheckIcon
          aria-hidden="true"
          className="h-5 w-5 text-primary flex-shrink-0"
        />
      )}
    </div>
  );

  const renderRow = (ent: T) => {
    const isSelected = selectedEntity?.id === ent.id;
    if (renderEntityRow) {
      return (
        <React.Fragment key={ent.id}>
          {renderEntityRow(ent, isSelected, toggleSelection)}
        </React.Fragment>
      );
    }
    return defaultRenderRow(ent, isSelected);
  };

  return (
    <Modal
      className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[80vw] md:w-[40vw] h-[80vh] flex flex-col rounded-lg p-0 bg-white shadow-xl"
      isOpen={isOpen}
      onRequestClose={onClose}
      overlayClassName="fixed inset-0 bg-black bg-opacity-30 z-[9999]"
    >
      <div className="flex-shrink-0 p-4 border-b flex items-center justify-between">
        <h2 className="text-lg font-semibold text-secondary">{headingText}</h2>
        {createEntity && (
          <Button
            className="bg-primary text-white hover:bg-primary-dark"
            onClick={() => setShowCreateDrawer(true)}
          >
            {`New ${entityNameSingular}`}
          </Button>
        )}
      </div>

      <div className="flex-shrink-0 p-4 border-b">
        <input
          className="border border-gray-300 rounded px-2 py-1 w-full"
          onChange={(e) => setSearchTerm(e.target.value)}
          placeholder={`Search ${entityNameSingular}...`}
          type="text"
          value={searchTerm}
        />
      </div>

      <div className="flex-1 overflow-auto p-4" id="infinite-scroller-parent">
        {isLoadingInitial ? (
          <div className="flex items-center justify-center w-full h-full">
            <LoadingIndicator isFullScreen={false} />
          </div>
        ) : (
          <InfiniteScroll
            dataLength={entities.length}
            endMessage={
              <p className="text-center mt-2">
                {`No more ${entityNamePlural}.`}
              </p>
            }
            hasMore={hasMore}
            loader={<p className="text-center mt-2">Loading more...</p>}
            next={fetchNext}
            scrollableTarget="infinite-scroller-parent"
            style={{ overflow: "visible" }}
          >
            <div className="space-y-2">
              {entities.map(renderRow)}
              {!isLoadingInitial && entities.length === 0 && (
                <p className="text-center text-gray-500">
                  {`No ${entityNamePlural} found.`}
                </p>
              )}
            </div>
          </InfiniteScroll>
        )}
      </div>

      <div className="flex-shrink-0 p-4 border-t flex justify-end space-x-3">
        <Button onClick={onClose} type="button" variant="outline">
          Cancel
        </Button>
        <Button
          className="bg-primary text-white hover:bg-primary-dark"
          onClick={() => onConfirm(selectedEntity)}
          type="button"
        >
          Confirm
        </Button>
      </div>

      {createEntity && (
        <CreateEntityDrawer
          createEntity={createEntity}
          entityNameSingular={entityNameSingular}
          isOpen={showCreateDrawer}
          onClose={() => setShowCreateDrawer(false)}
          onEntityCreated={handleEntityCreated}
        />
      )}
    </Modal>
  );
}
